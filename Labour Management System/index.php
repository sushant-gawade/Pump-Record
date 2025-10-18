<?php
// index.php

include 'config.php';

// Fetch all labours from the database
$sql = "SELECT labour_id, name FROM labours ORDER BY name ASC";
$result = $conn->query($sql);

$labours = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $labours[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Labour Management Dashboard</title>
    <style>
        /* Basic Layout CSS */
        body { font-family: Arial, sans-serif; margin: 0; background-color: #e9ecef; }
        .header { background-color: #007bff; color: white; padding: 15px; text-align: center; }
        .main-container { display: flex; max-width: 1200px; margin: 20px auto; gap: 20px; }
        .left-panel { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .right-panel { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

        /* Button and Search Styling */
        .btn-create { display: block; width: 100%; padding: 10px; background-color: #28a745; color: white; text-align: center; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .btn-create:hover { background-color: #218838; }
        #search-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }

        /* Labour List Styling */
        .labour-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .labour-item:last-child { border-bottom: none; }
        .labour-name { font-weight: bold; }
        .btn-view { padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em; }
        .btn-view:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Labour Management Dashboard</h1>
    </div>

    <div class="main-container">
        <div class="left-panel">
            <a href="create_profile.php" class="btn-create">➕ Create Profile</a>
            <h2>Labour Profile List</h2>
            <div id="labour-list">
                <?php if (count($labours) > 0): ?>
                    <?php foreach ($labours as $labour): ?>
                        <div class="labour-item" data-name="<?php echo strtolower($labour['name']); ?>">
                            <span class="labour-name"><?php echo htmlspecialchars($labour['name']); ?></span>
                            <a href="view_profile.php?id=<?php echo $labour['labour_id']; ?>" class="btn-view">View Profile</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No labour profiles found. Click 'Create Profile' to add one.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="right-panel">
            <h3>Search Labour</h3>
            <input type="text" id="search-input" placeholder="Search by name...">
        </div>
    </div>
    
    <script>
        document.getElementById('search-input').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.labour-item');

            items.forEach(function(item) {
                let name = item.getAttribute('data-name');
                if (name.includes(filter)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>