<?php
// Load the JSON data
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true); // Decode to associative array
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comic Book Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Comic Book Collection</h1>
        
        <h2 class="mt-4">Series</h2>
        <div class="list-group">
            <?php foreach ($comicsData['comics'] as $series => $issues) { ?>
                <!-- Link to detail.php passing the series name directly -->
                <a href="detail.php?series=<?php echo $series; ?>" class="list-group-item list-group-item-action">
                    <?php echo $series; ?> (<?php echo count($issues); ?> issues)
                </a>
            <?php } ?>
        </div>    
        <a href="create.php" class="btn btn-primary">Create a Post</a>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
