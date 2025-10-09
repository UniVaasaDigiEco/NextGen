# NextGen Food Waste Prediction Azure Function App

## Overview

This Azure Function App provides a machine learning-based solution to predict food waste for a restaurant or kitchen. It includes functionalities for training models based on historical data from an Azure SQL database and making predictions for future dates. The app is designed to be deployed as an Azure Function, with support for local development and testing.

The core logic is contained within `function_app.py`.

## Features

- **Train Models**: An HTTP endpoint to trigger model training on historical data.
- **Predict Waste**: An HTTP endpoint to get food waste predictions for a future period, with support for both string and JSON output.
- **Get Model Info**: An HTTP endpoint to retrieve information about existing trained models for a specific date without retraining.
- **Automated Daily Retraining**: A timer-triggered function that automatically retrains the models every day to keep them up-to-date.
- **Dynamic Model Versioning**: Models are saved in date-stamped folders, allowing for versioning and traceability.
- **On-Demand Training**: If a prediction is requested for a date without pre-trained models, the app automatically triggers the training process for that date first.

## Endpoints

The application exposes three main functions:

### 1. Train Waste Prediction Model

This function trains the machine learning models for plate waste (`Lautashavikki`), kitchen waste (`Keittiohavikki`), and diner count (`Ruokailijamäärä`). It returns a JSON object with details about the training run.

- **Route**: `/api/train_waste_prediction_model`
- **Method**: `GET`
- **Query Parameters**:
  - `start_date` (optional): The start date for the training data range. Format: `YYYY-MM-DD`. Defaults to `2023-02-16`.
  - `end_date` (optional): The end date for the training data range. Format: `YYYY-MM-DD`. Defaults to the current date.
  - `num_days` (optional): The number of days of historical data to use for training. Defaults to `7`.

### 2. Waste Prediction

This function provides predictions for a specified number of days starting from a given date.

- **Route**: `/api/waste_prediction`
- **Method**: `GET`
- **Query Parameters**:
  - `prediction_start_date` (optional): The date for which to start the prediction. Format: `YYYY-MM-DD`. Defaults to the current date.
  - `num_days` (optional): The number of future days to predict. Defaults to `7`.
  - `json_format` (optional): If set to `true`, the output will be in JSON format. Defaults to `false` (string format).

### 3. Get Model Information

This function returns information about models that have already been trained and saved for a specific date.

- **Route**: `/api/get_model_info`
- **Method**: `GET`
- **Query Parameters**:
  - `request_date` (optional): The date for which to retrieve model information. Format: `YYYY-MM-DD`. Defaults to the current date.

### 4. Daily Training Timer

This is a timer-triggered function that runs automatically to keep the models fresh.

- **Schedule**: Runs every day at 1:00 AM UTC (`0 0 1 * * *`).
- **Action**: Calls the `train_waste_prediction_model` endpoint with default parameters to retrain the models on the latest available data.

## Model Storage

- When `train_waste_prediction_model` is executed, it creates a new folder named after the `end_date` (e.g., `2025-09-05/`) inside a main models directory.
- All model artifacts for that training run (`.pkl` files for encoders and models, and a `training_info.json` file) are saved into this date-specific folder.
- When `waste_prediction` is called, it looks for models in the folder corresponding to the `prediction_start_date`.

## Configuration

The application requires several environment variables to be set for database connections and other settings. These should be configured in `local.settings.json` for local development and in the Application Settings for the deployed Azure Function App.

- `FUNCTIONS_WORKER_RUNTIME`: The runtime environment for Azure Functions (e.g., `python`).
- `AZURE_SQL_SERVER`: The server name of the Azure SQL database.
- `AZURE_SQL_PORT`: The port for the Azure SQL database (e.g., `1433`).
- `AZURE_SQL_DATABASE`: The name of the database.
- `AZURE_SQL_USER`: The username for database access.
- `AZURE_SQL_PASSWORD`: The password for the database user.
- `LOCAL_DEVELOPMENT`: Set to `true` for local development to ensure models are saved in a local `models/` directory. In Azure, this should be `false` or not present, and a file share should be mounted to `/models/`.

In order to make the application work seamlessly in both local and Azure environments, you should ensure that the necessary environment variables are set correctly in each context.

Following variables are needed for the timer function to work:
- `AzureWebJobsStorage`: Connection string for the Azure Storage account used by the Function App.
- `AzureWebJobsFeatureFlags`: Feature flags for Azure Functions.
- `StorageConnection`: Connection string for the Azure Storage account used for model storage.


## Setup and Running Locally

1.  **Prerequisites**:
    - Python 3.9+
    - Azure Functions Core Tools
    - Microsoft ODBC 18 for SQL Server driver 
    
2.  **Install Dependencies**:
    ```bash
    pip install -r requirements.txt
    ```

3.  **Configure Local Settings**:
    - Copy the structure of `local.settings.json.sample` (if available) or create a `local.settings.json` file.
    - Fill in the values for the environment variables listed in the **Configuration** section.
    - Sample:
    {
      "IsEncrypted": false,
      "Values": {
        "FUNCTIONS_WORKER_RUNTIME": "python",
        "AZURE_SQL_SERVER": "your_server_name",
        "AZURE_SQL_PORT": "1433",
        "AZURE_SQL_DATABASE": "your_database_name",
        "AZURE_SQL_USER": "your_username",
        "AZURE_SQL_PASSWORD": "your_password",
        "LOCAL_DEVELOPMENT": "true",
      },
        "Host": {
        "LocalHttpPort": 7071,
        "CORS": "*",
        "CORSCredentials": false
      }
    }

    - Working with Azure Portal: Add these values with your appropriate values from your own azure environment
      "Values": {
        "AzureWebJobsStorage": "DefaultEndpointsProtocol=...",
        "AzureWebJobsFeatureFlags": "EnableWorkerIndexing",
        "StorageConnection": "DefaultEndpointsProtocol=...",
        "FUNCTIONS_WORKER_RUNTIME": "python",
        ...
      }

4.  **Run the Function App**:
    ```bash
    func start
    ```
