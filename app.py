import numpy as np
import pandas as pd
import MySQLdb
import pmdarima as pm
from datetime import datetime, timedelta
import json
from sklearn.preprocessing import MinMaxScaler
from tensorflow import keras
from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.tsa.seasonal import seasonal_decompose
import os
import subprocess
import sys
from geopy.distance import geodesic

def check_tensorflowjs_converter():
    """Check if tensorflowjs_converter is installed and accessible"""
    try:
        subprocess.run(['tensorflowjs_converter', '--version'], 
                      capture_output=True, 
                      check=True)
        return True
    except (subprocess.SubprocessError, FileNotFoundError):
        print("Error: tensorflowjs_converter not found. Please install it using:")
        print("pip install tensorflowjs")
        return False

def convert_to_tfjs(model_path, output_path, province, city):
    """
    Convert Keras model to TensorFlow.js format
    
    Parameters:
        model_path (str): Path to the saved Keras model (.h5 file)
        output_path (str): Directory to save the converted model
        province (str): Province name for logging
        city (str): City name for logging
        
    Returns:
        bool: True if conversion was successful, False otherwise
    """
    try:
        # Construct the conversion command
        command = [
            'tensorflowjs_converter',
            '--input_format=keras',
            model_path,
            output_path
        ]
        
        # Run the conversion
        result = subprocess.run(
            command,
            capture_output=True,
            text=True,
            check=True
        )
        
        print(f"Successfully converted model for {province}/{city}")
        return True
        
    except subprocess.CalledProcessError as e:
        print(f"Error converting model for {province}/{city}:")
        print(f"Command output: {e.output}")
        return False
        
    except Exception as e:
        print(f"Unexpected error converting model for {province}/{city}: {str(e)}")
        return False

def parse_latlng(latlng_str):
    """Parse LatLng string into latitude and longitude components"""
    try:
        if pd.isna(latlng_str):
            return None, None
        # Remove any parentheses and split by comma
        cleaned = latlng_str.strip('()[] ').split(',')
        if len(cleaned) == 2:
            return float(cleaned[0].strip()), float(cleaned[1].strip())
        return None, None
    except:
        return None, None

def load_and_prepare_data(connection):
    """Load and prepare data from MySQL database including geographic data"""
    query = """
    SELECT 
        t.TransacID,
        t.TotalPrice,
        DATE(t.TransactionDate) AS TransactionDate,
        t.Status,
        l.City,
        l.Province,
        l.LatLng,
        t.DeliveryFee
    FROM TransacTb t
    JOIN LocationTb l ON t.LocationID = l.LocationID
    WHERE t.Status = 'Delivered'
    """
    
    df = pd.read_sql(query, connection)
    df['TransactionDate'] = pd.to_datetime(df['TransactionDate'])
    
    # Parse LatLng into separate columns
    df['Latitude'], df['Longitude'] = zip(*df['LatLng'].apply(parse_latlng))
    
    return df

def analyze_geographic_patterns(df):
    """Analyze sales patterns with geographic considerations"""
    # Group by location for city-level analysis
    city_analysis = df.groupby(['City', 'Province', 'Latitude', 'Longitude']).agg({
        'TotalPrice': ['count', 'sum', 'mean'],
        'DeliveryFee': ['mean', 'sum']
    }).reset_index()
    
    # Rename columns for clarity
    city_analysis.columns = [
        'City', 'Province', 'Latitude', 'Longitude',
        'OrderCount', 'TotalRevenue', 'AvgOrderValue',
        'AvgDeliveryFee', 'TotalDeliveryFees'
    ]
    
    # Calculate revenue per order for cities
    city_analysis['RevenuePerOrder'] = city_analysis['TotalRevenue'] / city_analysis['OrderCount']
    
    # Calculate province-level totals
    province_analysis = df.groupby('Province').agg({
        'TotalPrice': ['count', 'sum', 'mean'],
        'DeliveryFee': ['mean', 'sum']
    }).reset_index()
    
    province_analysis.columns = [
        'Province',
        'TotalOrderCount', 'TotalRevenue', 'AvgOrderValue',
        'AvgDeliveryFee', 'TotalDeliveryFees'
    ]
    
    # Calculate revenue per order for provinces
    province_analysis['RevenuePerOrder'] = province_analysis['TotalRevenue'] / province_analysis['TotalOrderCount']
    
    # Find distance between locations and analyze delivery fees
    locations = []
    for idx, row in city_analysis.iterrows():
        if row['Latitude'] is not None and row['Longitude'] is not None:
            distances = []
            delivery_fees = []
            for idx2, row2 in city_analysis.iterrows():
                if idx != idx2 and row2['Latitude'] is not None and row2['Longitude'] is not None:
                    distance = geodesic(
                        (row['Latitude'], row['Longitude']),
                        (row2['Latitude'], row2['Longitude'])
                    ).kilometers
                    distances.append(distance)
                    delivery_fees.append(row2['AvgDeliveryFee'])
            
            # Get province totals for this city
            province_totals = province_analysis[
                province_analysis['Province'] == row['Province']
            ].iloc[0]
            
            locations.append({
                'city': row['City'],
                'province': row['Province'],
                'coordinates': [row['Latitude'], row['Longitude']],
                'metrics': {
                    'city_metrics': {
                        'total_revenue': float(row['TotalRevenue']),
                        'order_count': int(row['OrderCount']),
                        'avg_order_value': float(row['AvgOrderValue']),
                        'avg_delivery_fee': float(row['AvgDeliveryFee']),
                        'revenue_per_order': float(row['RevenuePerOrder']),
                        'percentage_of_province_revenue': float(row['TotalRevenue'] / province_totals['TotalRevenue'] * 100)
                    },
                    'province_metrics': {
                        'total_revenue': float(province_totals['TotalRevenue']),
                        'order_count': int(province_totals['TotalOrderCount']),
                        'avg_order_value': float(province_totals['AvgOrderValue']),
                        'avg_delivery_fee': float(province_totals['AvgDeliveryFee']),
                        'revenue_per_order': float(province_totals['RevenuePerOrder'])
                    }
                },
                'distances': {
                    'avg_distance_to_others': np.mean(distances) if distances else 0,
                    'max_distance': max(distances) if distances else 0,
                    'min_distance': min(distances) if distances else 0
                }
            })
    
    return locations

def prepare_city_data(df, city):
    """Prepare time series data for a specific city"""
    city_data = df[df['City'] == city].copy()
    daily_sales = city_data.groupby('TransactionDate').agg({
        'TotalPrice': 'sum',
        'DeliveryFee': 'sum'
    }).reset_index()
    
    daily_sales = daily_sales.set_index('TransactionDate')
    daily_sales = daily_sales.resample('D').asfreq().fillna(0)
    
    # Add time-based features
    daily_sales['DayOfWeek'] = daily_sales.index.dayofweek
    daily_sales['Month'] = daily_sales.index.month
    daily_sales['Year'] = daily_sales.index.year
    
    return daily_sales

def create_lstm_data(series, seq_length=7):
    """
    Create sequences for LSTM training from time series data.
    
    Parameters:
        series (pd.Series): Time series data
        seq_length (int): Length of input sequences
        
    Returns:
        X (np.array): Input sequences
        y (np.array): Target values
        scaler (MinMaxScaler): Fitted scaler for inverse transformation
    """
    # Scale the data
    scaler = MinMaxScaler(feature_range=(0, 1))
    scaled_data = scaler.fit_transform(series.values.reshape(-1, 1))
    
    # Create sequences
    X = []
    y = []
    
    for i in range(len(scaled_data) - seq_length):
        X.append(scaled_data[i:(i + seq_length), 0])
        y.append(scaled_data[i + seq_length, 0])
    
    # Convert to numpy arrays
    X = np.array(X)
    y = np.array(y)
    
    # Reshape X to match LSTM input shape [samples, time steps, features]
    X = np.reshape(X, (X.shape[0], X.shape[1], 1))
    
    return X, y, scaler

def create_lstm_model(seq_length):
    """Create LSTM model with geographic features"""
    model = keras.Sequential([
        keras.layers.LSTM(64, activation='relu', input_shape=(seq_length, 1), return_sequences=True),
        keras.layers.Dropout(0.2),
        keras.layers.LSTM(32, activation='relu'),
        keras.layers.Dropout(0.2),
        keras.layers.Dense(16, activation='relu'),
        keras.layers.Dense(1)
    ])
    model.compile(optimizer='adam', loss='mse')
    return model

def main():
    # Check for tensorflowjs_converter
    if not check_tensorflowjs_converter():
        sys.exit(1)
    
    # Create output directories
    os.makedirs('lstm_models', exist_ok=True)
    os.makedirs('tfjs_models', exist_ok=True)
    
    try:
        # Connect to MySQL database
        connection = MySQLdb.connect(
            host='localhost',
            user='root',
            passwd='',
            db='storedb'
        )
        print("Successfully connected to MySQL database")
        
        # Load data
        df = load_and_prepare_data(connection)
        
        # Analyze geographic patterns
        geographic_analysis = analyze_geographic_patterns(df)
        
        # Save geographic analysis
        with open('geographic_analysis.json', 'w') as f:
            json.dump(geographic_analysis, f, indent=2)
        
        # Get unique province-city combinations
        location_data = df.groupby(['Province', 'City']).first().reset_index()
        city_results = {}
        conversion_success = True
        
        for _, row in location_data.iterrows():
            province = row['Province']
            city = row['City']
            print(f"\nProcessing {province}/{city}")
            
            # Create clean directory names
            province_dir = province.upper().replace(" ", "_")
            city_dir = city.upper().replace(" ", "_")
            
            # Create nested directory structure for tfjs models
            tfjs_path = os.path.join('tfjs_models', province_dir, city_dir)
            os.makedirs(tfjs_path, exist_ok=True)
            
            # Create nested directory structure for lstm models
            lstm_path = os.path.join('lstm_models', province_dir)
            os.makedirs(lstm_path, exist_ok=True)

            # Get city coordinates
            city_coords = {
                'latitude': row['Latitude'],
                'longitude': row['Longitude']
            }
            
            # Prepare city data
            daily_sales = prepare_city_data(df, city)
            
            # Train SARIMA model
            sarima_model = SARIMAX(
                daily_sales['TotalPrice'],
                order=(1, 1, 1),
                seasonal_order=(1, 1, 1, 7)
            ).fit(disp=False)
            
            sarima_forecast = sarima_model.forecast(30)
            
            # Train LSTM model
            X, y, scaler = create_lstm_data(daily_sales['TotalPrice'])
            lstm_model = create_lstm_model(7)
            lstm_model.fit(X, y, epochs=50, batch_size=32, verbose=0)
            
            # Save and convert model
            lstm_model_path = os.path.join(lstm_path, f'lstm_model_{city_dir}.h5')
            lstm_model.save(lstm_model_path)
            
            # Convert LSTM model to TFJS format
            if not convert_to_tfjs(lstm_model_path, tfjs_path, province, city):
                print(f"Failed to convert model for {province}/{city}")
                continue
            
            # Store results
            if province not in city_results:
                city_results[province] = {}
            
            city_results[province][city] = {
                'coordinates': city_coords,
                'forecasts': {
                    'sarima': sarima_forecast.tolist(),
                    'dates': [(datetime.now() + timedelta(days=x)).strftime('%Y-%m-%d') 
                             for x in range(30)]
                },
                'patterns': {
                    'daily': daily_sales.groupby('DayOfWeek')['TotalPrice'].mean().to_dict(),
                    'monthly': daily_sales.groupby('Month')['TotalPrice'].mean().to_dict(),
                    'delivery_fees': {
                        'average': float(daily_sales['DeliveryFee'].mean()),
                        'total': float(daily_sales['DeliveryFee'].sum())
                    }
                },
                'scaler_params': {
                    'scale_': scaler.scale_.tolist(),
                    'min_': scaler.min_.tolist()
                }
            }
        
        # Save all results
        with open('analysis_results.json', 'w') as f:
            json.dump(city_results, f, indent=2)
            
        if not conversion_success:
            print("\nWarning: Some model conversions failed. Please check the logs above.")
            
    except MySQLdb.Error as e:
        print(f"Error connecting to MySQL database: {e}")
        
    finally:
        if 'connection' in locals():
            connection.close()
            print("MySQL connection closed")

if __name__ == "__main__":
    main()