<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notificación</title>
</head>
<body>
    <h3>{{ $data['subject'] }}</h3>
    <h3>De: {{ $data['name'] }}</h3>
    <h3># doc: {{ $data['document'] }}</h3>
    <h4>{{ $data['message'] }}</h4>
    <!-- <p>Gracias por usar nuestro servicio.</p> -->
</body>
</html>
