{{-- docs --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>welcome</title>

    {{-- cdn bootstrap 3 --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    {{-- cdn bootstrap 4 --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    {{-- cdn fontawesome --}}
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
        integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">

    {{-- cdn jquery --}}
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>

    {{-- cdn popper --}}

</head>

<body style="background-color: #f5f5f5">
    <h1 class="text-center">API Documentation</h1>
    <h2 class="text-center">Version 1.0</h2>
    <h3 class="text-center">Base URL: {{ $data['base_url'] }}</h3>
    <hr style="width: 50%; margin: 0 auto;">

    <div class="container">
        <div class="row"></div>
            <div class="card col-md-12">
                <div class="card-header mt-3 alert alert-info">
                    <h3 class="text-center">Endpoints</h3>
                </div>
                <h2 class="well alert alert-success">Auth</h2>
                <ul>
                    @foreach ($data['endpoints']['auth'] as $endpoint)
                        <li class="well well-sm">{{ $endpoint }}</li>
                    @endforeach
                </ul>

                <h2 class="well alert alert-success">Suppliers</h2>
                <ul>
                    @foreach ($data['endpoints']['suppliers'] as $endpoint)
                        <li class="well well-sm">{{ $endpoint }}</li>
                    @endforeach
                </ul>

                <h2 class="well alert alert-success">Compoents</h2>
                <ul>
                    @foreach ($data['endpoints']['components'] as $endpoint)
                        <li class="well well-sm">{{ $endpoint }}</li>
                    @endforeach
                </ul>

                {{-- restocks --}}
                <h2 class="well alert alert-success">Restocks</h2>
                <ul>
                    @foreach ($data['endpoints']['restocks'] as $endpoint)
                        <li class="well well-sm">{{ $endpoint }}</li>
                    @endforeach
                </ul>


                {{-- sales --}}
                <h2 class="well alert alert-success">Sales</h2>
                <ul>
                    @foreach ($data['endpoints']['sales'] as $endpoint)
                        <li class="well well-sm">{{ $endpoint }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

</body>

</html>
