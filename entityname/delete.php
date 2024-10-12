<?php
session_start();

//Check if the user is logged in
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['redirect_url'] = 'delete.php'; //saves the current page to redirect back to
    header('Location: login.php');
    exit;
}

//load data from json
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true); //Decode JSON to array

//initialize message
$message = '';

//Handle deletion of a comic series or issue
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $type = $_POST['delete_type']; // series or issue
    $seriesTitle = $_POST['series'];

    if ($type == 'series'){
        //Delete the entire series
        if (array_key_exists($seriesTitle, $comicsData['comics'])){
            unset($comicsData['comics'][$seriesTitle]); //removing series from array
            $message = "Series '{$seriesTitle}' has been deleted.";
        } else {
            $message = "Series '{$seriesTitle}' not found.";
        }
    } elseif ($type == 'issue'){
        //Delete a specific issue from a series
        $issueTitle = $_POST['issue'];
        if (array_key_exists($seriesTitle, $comicsData['comics'])){
            foreach ($comicsData['comics'][$seriesTitle] as $key => $issue){
                if ($issue['title'] === $issueTitle){
                    unset($comicsData['comics'][$seriesTitle][$key]); // Remove the issue
                    $message = "Issue '{$issueTitle}' from series '{$seriesTitle}' has been deleted.";
                    break;
                }
            }
        } else {
            $message = "Series '{$seriesTitle}' not found.";
        }
    }
    //save the updated data back to the json file
    file_put_contents('comics.json', json_encode($comicsData, JSON_PRETTY_PRINT));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Comic Entity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Delete Comic Entity</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <h2>Delete a Comic Series</h2>
        <form action="delete.php" method="POST">
            <div class="mb-3">
                <label for="series" class="form-label">Select Series to Delete</label>
                <select class="form-select" id="series" name="series" required>
                    <option value="">Select a series</option>
                    <?php foreach (array_keys($comicsData['comics']) as $series): ?>
                        <option value="<?php echo htmlspecialchars($series); ?>"><?php echo htmlspecialchars($series); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="delete_type" value="series">
            <button type="submit" class="btn btn-danger">Delete Series</button>
        </form>

        <h2 class="mt-4">Delete a Comic Issue</h2>
        <form action="delete.php" method="POST">
            <div class="mb-3">
                <label for="series" class="form-label">Select Series</label>
                <select class="form-select" id="series" name="series" required onchange="updateIssueDropdown(this.value)">
                    <option value="">Select a series</option>
                    <?php foreach (array_keys($comicsData['comics']) as $series): ?>
                        <option value="<?php echo htmlspecialchars($series); ?>"><?php echo htmlspecialchars($series); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="issue" class="form-label">Select Issue to Delete</label>
                <select class="form-select" id="issue" name="issue" required>
                    <option value="">Select an issue</option>
                    <!-- Issues will be populated by JavaScript -->
                </select>
            </div>
            <input type="hidden" name="delete_type" value="issue">
            <button type="submit" class="btn btn-danger">Delete Issue</button>
        </form>

        <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>

    <script>
        // JavaScript to dynamically update the issue dropdown based on selected series
        function updateIssueDropdown(series) {
            var issueDropdown = document.getElementById('issue');
            issueDropdown.innerHTML = '<option value="">Select an issue</option>'; // Reset the dropdown

            <?php foreach ($comicsData['comics'] as $series => $issues): ?>
                if (series === "<?php echo $series; ?>") {
                    <?php foreach ($issues as $issue): ?>
                        issueDropdown.innerHTML += '<option value="<?php echo htmlspecialchars($issue['title']); ?>"><?php echo htmlspecialchars($issue['title']); ?></option>';
                    <?php endforeach; ?>
                }
            <?php endforeach; ?>
        }
    </script>
</body>
</html>
