<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Subscription Confirmation</title>
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
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .unsubscribe {
            background-color: #dc2626;
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
    <h1>Weather Updates Subscription</h1>

    <p>Hello!</p>

    <p>You have requested to receive weather updates for <strong>{{ $subscription->city }}</strong> with <strong>{{ $subscription->frequency->value }}</strong> frequency.</p>

    <p>To confirm your subscription, please click the button below:</p>

    <a href="{{ $confirmUrl }}" class="button">Confirm Subscription</a>

    <p>If you did not request this subscription, or wish to cancel it, you can use the link below:</p>

    <a href="{{ $unsubscribeUrl }}" class="button unsubscribe">Cancel Subscription</a>

    <p>Thank you for using our Weather Updates service!</p>

    <div class="footer">
        <p>This email was sent automatically. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Weather Updates Service</p>
    </div>
</body>
</html>
