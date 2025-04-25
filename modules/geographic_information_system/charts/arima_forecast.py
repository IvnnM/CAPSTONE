import sys
import json
import numpy as np
from statsmodels.tsa.arima.model import ARIMA

def run_arima_forecast(data):
    # Combine both years of historical data
    historical_revenue = [item[1] for item in data.values()]
    
    # Convert to numpy array
    historical_revenue = np.array(historical_revenue)
    
    # Train ARIMA model
    model = ARIMA(historical_revenue, order=(5, 1, 0))  # ARIMA(p,d,q)
    model_fit = model.fit()

    # Forecast for the next 3 months
    forecast = model_fit.forecast(steps=3)
    
    return forecast.tolist()

if __name__ == '__main__':
    # Get input data (historical sales) from the PHP script
    input_data = json.loads(sys.argv[1])
    
    # Run ARIMA forecast
    forecast = run_arima_forecast(input_data)
    
    # Return the forecasted result as JSON
    print(json.dumps(forecast))
