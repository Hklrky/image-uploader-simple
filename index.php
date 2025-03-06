<?php
// Define the target directory for uploads
$targetDir = "images/";
$maxFileSize = 10 * 1024 * 1024; // 10MB
$message = "Made with ♥️ by Daven, Max up 10MB";
$jsonFilePath = 'link.json'; // Path to the JSON file

// Initialize an array to store uploaded file URLs
$uploadedUrls = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if files are uploaded
    if (isset($_FILES['files']) && count($_FILES['files']['name']) > 0) {
        $totalFiles = count($_FILES['files']['name']);
        
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['files']['error'][$i] == 0) {
                $file = $_FILES['files'];
                
                // Validate file size
                if ($file['size'][$i] > $maxFileSize) {
                    $message = "Error: File size exceeds 10MB for file " . $file['name'][$i] . ".";
                    continue;
                }

                // Validate file type
                $fileType = strtolower(pathinfo($file['name'][$i], PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($fileType, $allowedTypes)) {
                    $message = "Error: Invalid file type for file " . $file['name'][$i] . ". Only JPG, JPEG, PNG, and GIF files are allowed.";
                    continue;
                }

                // Generate a random file name
                $randomName = bin2hex(random_bytes(5)) . '.' . $fileType;
                $targetFilePath = $targetDir . $randomName;

                // Move the uploaded file to the target directory
                if (move_uploaded_file($file['tmp_name'][$i], $targetFilePath)) {
                    $uploadedUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $targetFilePath;
                    $uploadedUrls[] = $uploadedUrl; // Store the uploaded URL
                    $message = "File uploaded successfully: " . $file['name'][$i];

                    // Save the uploaded URL to link.json
                    if (file_exists($jsonFilePath)) {
                        $existingData = json_decode(file_get_contents($jsonFilePath), true);
                        $existingData = is_array($existingData) ? $existingData : [];
                    } else {
                        $existingData = [];
                    }

                    // Add the new URL to the existing data
                    $existingData[] = $uploadedUrl;

                    // Save the updated data back to the JSON file
                    file_put_contents($jsonFilePath, json_encode($existingData, JSON_PRETTY_PRINT));
                } else {
                    $message = "Error: There was an error uploading file " . $file['name'][$i] . ".";
                }
            } else {
                $message = "Error: No file uploaded or there was an upload error for file " . $file['name'][$i] . ".";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #e0e7ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            transition: transform 0.3s;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #4F46E5;
        }
        .message {
            margin-top: 1rem;
            color: #333;
            font-weight: bold;
        }
        .upload-button {
            background-color: #4F46E5;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .upload-button:hover {
            background-color: #4338CA;
        }
        .link-container {
            margin-top: 1rem;
        }
        .copy-button {
            background-color: #4F46E5;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background-color 0.3s;
        }
        .copy-button:hover {
            background-color: #4338CA;
        }
    </style>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard!');
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Upload Your Images</h1>
        <form id="uploadForm" enctype="multipart/form-data" method="POST">
            <input type="file" name="files[]" id="file" required multiple accept="image/*" />
            <br><br>
            <button type="submit" class="upload-button">Upload</button>
        </form>
        <div class="message">
            <?php if ($message) echo $message; ?>
        </div>
        <div class="link-container">
            <?php if (!empty($uploadedUrls)): ?>
                <h3>Uploaded Image Links:</h3>
                <?php foreach ($uploadedUrls as $url): ?>
                    <div>
                        <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a>
                        <button class="copy-button" onclick="copyToClipboard('<?php echo $url; ?>')">Copy Link</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>