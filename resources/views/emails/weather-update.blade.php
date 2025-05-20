<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2563eb;
        }
        .weather-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            background-color: #f8fafc;
        }
        .temperature {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
        }
        .description {
            margin: 10px 0;
            color: #475569;
        }
        .details {
            display: flex;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .detail-item {
            flex: 1 0 50%;
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Weather Update for {{ $subscription->city }}</h1>

    <p>Hello!</p>

    <p>Here is your {{ $subscription->frequency->value }} weather update for {{ $subscription->city }}:</p>

    <div class="weather-card">
        <div class="temperature">{{ $weatherData['temperature'] }}°C</div>
        <div class="humidity">{{ $weatherData['humidity'] }}</div>
        <div class="description">{{ $weatherData['description'] }}</div>
    </div>

    <p>This update was sent in accordance with your subscription preferences.</p>

    <p>If you'd like to unsubscribe from these updates, please click the button below:</p>

    <a href="{{ $unsubscribeUrl }}" class="button">Unsubscribe</a>

    <div class="footer">
        <p>This email was sent automatically. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Weather Updates Service</p>
    </div>
</body>
</html>
