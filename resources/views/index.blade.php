<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Weather Forecast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f8fa;
        }
        .weather-card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            display: none;
        }
        .weather-card:hover {
            transform: translateY(-5px);
        }
        #subscription-section {
            display: none;
            margin-top: 20px;
        }
        .loader {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Форма пошуку погоди -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h1 class="text-center mb-0">Current Weather Forecast</h1>
                    </div>
                    <div class="card-body">
                        <form id="weather-form">
                            <div class="mb-3">
                                <label for="city" class="form-label">Enter Location</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       placeholder="Enter city name" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Get Weather</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Індикатор завантаження -->
                <div class="loader" id="weather-loader">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Fetching weather data...</p>
                </div>

                <!-- Результати погоди -->
                <div id="weather-results" class="card weather-card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 id="temperature"></h3>
                                <p id="humidity"></p>
                                <p id="description"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Секція підписки -->
                <div id="subscription-section">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h3 class="mb-0">Subscribe to Weather Updates</h3>
                        </div>
                        <div class="card-body">
                            <p>Would you like to receive regular weather updates?</p>
                            <form id="subscription-form">
                                <div class="mb-3">
                                    <label for="sub-city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="sub-city" name="city" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Frequency</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="frequency" id="hourly" value="hourly" checked>
                                        <label class="form-check-label" for="hourly">
                                            Hourly
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="frequency" id="daily" value="daily">
                                        <label class="form-check-label" for="daily">
                                            Daily
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">Subscribe</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Повідомлення про помилки і успіхи -->
                <div id="weather-error" class="alert alert-danger mt-3" style="display: none;"></div>
                <div id="subscription-success" class="alert alert-success mt-3" style="display: none;"></div>
                <div id="subscription-error" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#weather-form').on('submit', function(e) {
                e.preventDefault();

                const city = $('#city').val();

                $('#weather-error').hide();

                $('#weather-loader').show();
                $('#weather-results').hide();
                $('#subscription-section').hide();

                $.ajax({
                    url: '{{ route("api.weather") }}',
                    type: 'GET',
                    data: {
                        city: city
                    },
                    success: function(response) {
                        $('#weather-loader').hide();

                        $('#temperature').text(response.temperature + '°C');
                        $('#humidity').text('Humidity: ' + response.humidity + '%');
                        $('#description').text('Description: ' + response.description);

                        $('#weather-results').fadeIn();

                        $('#sub-city').val(city);

                        $('#subscription-section').fadeIn();
                    },
                    error: function(xhr) {
                        $('#weather-loader').hide();

                        let errorMsg = 'Failed to get weather data';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors && xhr.responseJSON.errors.city) {
                                errorMsg = xhr.responseJSON.errors.city[0];
                            } else if (xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        }

                        $('#weather-error').text(errorMsg).show();
                    }
                });
            });

            $('#subscription-form').on('submit', function(e) {
                e.preventDefault();

                $('#subscription-success').hide();
                $('#subscription-error').hide();

                const city = $('#sub-city').val();
                const email = $('#email').val();
                const frequency = $('input[name="frequency"]:checked').val();

                $.ajax({
                    url: '{{ route("api.subscribe") }}',
                    type: 'POST',
                    data: {
                        city: city,
                        email: email,
                        frequency: frequency,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#subscription-success').text('Successfully subscribed to weather updates!').show();

                        $('#subscription-form')[0].reset();
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to subscribe';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#subscription-error').text(errorMsg).show();
                    }
                });
            });
        });
    </script>
</body>
</html>
