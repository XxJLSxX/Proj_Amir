<?php
    require '../Database/MoistFunctions.php';
    $moistFunctions = new MoistFunctions($connection);

    $add_Games = $moistFunctions -> showRecords('developer');
    
    if (isset($_POST['Add'])){
        $data = [];
        $Gname = $_POST['Game_Name'];
        
        //Input Date in Database Table
        $data['Game_Downloads'] = '0';
        $data['Upload_Date'] = date('Y-m-d');
        $data['Game_Rating'] = '0';
        foreach ($_POST as $name => $val) {
            if ($name !== 'Add' && $name !== 'GameImage' && $name !== 'GameBackground' && $name !== 'Screenshot1' && $name !== 'Screenshot2' && $name !== 'Screenshot3') {
                $data[$name] = $val;
            }
        }
        //Create Folder
        $folderPath = "../Games/$Gname";
        if (!is_dir($folderPath)) {
            //Create if existing
            mkdir($folderPath,0777);
        }else {
            echo"Game Already Exists";
            die();
        }
        try {
            $action = $moistFunctions->addQuery($data, 'games');
        } catch (Exception $e) {
            echo "Error: $e";
            die();
        } 
        
        //Save and Rename image for GameImage
        $target_dir = "../Games/$Gname/";
        $moistFunctions -> uploadFile($_FILES["GameImage"], $target_dir, $Gname, "Image." . "png");
        $moistFunctions -> uploadFile($_FILES["GameBackground"], $target_dir, $Gname, "Background." . "png");
        $moistFunctions -> uploadFile($_FILES["Screenshot1"], $target_dir, $Gname, "Screenshot1." . "png");
        $moistFunctions -> uploadFile($_FILES["Screenshot2"], $target_dir, $Gname, "Screenshot2." . "png");
        $moistFunctions -> uploadFile($_FILES["Screenshot3"], $target_dir, $Gname, "Screenshot3." . "png");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <title>Add Game</title>
    <style>
        body{
            font-family: 'Montserrat', sans-serif;
            justify-content: center;
        }
        .gamesAdd-container{
            text-align: center; 
            background-color: #5d5d5d;
            border-radius: 25px;
            padding: 2%;
            width: 500px;
            margin: 40px;
            margin-left: auto; 
            margin-right: auto;
        }
        .gamesAdd-container p{
            font-weight: bold;
            color: white;
            font-size: 16px;
        }
        .gamesAdd-container select{
            border-radius: 16.5px;
            width: 420px;
            padding: 7.5px;
            background-color: #dddddd;
        }
        .gamesAdd-container label{
            margin-left: 16.5px;
            color: white;
            font-size: 15px;
            font-weight: lighter;
            float: left; /* Align text to the left */
        }
        .gamesAdd-container input{
            border-radius: 16.5px;
            width: 420px;
            padding: 7.5px;
        }
        .gamesAdd-container textarea {
            border-radius: 7.5px;
            padding: 8.5px;
            width: 416px; /* Adjust the width as needed */
            height: 150px; /* Adjust the height as needed */
        }
        .gamesAdd-container .submit-button {
            font-weight: bold;
            background-color: #ababab;
            border: none;
            border-radius: 25px;
            color: black;
            font-size: 16px;
            padding: 10px 20px;
            text-decoration: none;
            width: 170px; /* Adjusted width */
            display: block;
            margin-top: 7.5px;
            margin-left: auto; /* Aligns the button to the right */
            margin-right: auto;
        }
        .file-upload{
            display: none;
            margin-left: 8px;
            border-radius: 16.5px;
            width: 420px;
            padding: 7.5px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 16px;
            background-color: white;
            cursor: pointer;
        }   
        
        .gamesAdd-container #inputFile::-webkit-file-upload-button {
            visibility: hidden;
        }
        .gamesAdd-container input[type='file'] {
            text-align: center;
            overflow: hidden;
        }
   
    </style>
    <script>
        document.getElementById("inputFile").addEventListener("change", function() {
        if (this.value) {
            this.setAttribute("data-title", this.value.replace(/^.*[\\\/]/, ''));
        } else {
            this.setAttribute("data-title", "No file chosen");
        }
        });
    </script>
</head>
<body style="background-color: #1e1e1e">
    <div class="gamesAdd-container">
        <form action="" method="post" enctype="multipart/form-data">
            <p style="font-size: 25px; margin-top: 16px; margin-bottom: 29px">Add a New Game</p>
            
            <label for="name">Game Name</label><br>
            <input type="text" name="Game_Name" required><br><br>
            
            <label for="developer">Game Developer</label><br>
            <select name="Developer_ID" class="form-select" onmousedown="this.size=5;" onclick="this.size=1" required>
                <option value="" disabled selected>Select Game Developer</option>
                <?php
                    if (count($add_Games) > 0) {
                        foreach ($add_Games as $dev) {
                            echo "<option value ='$dev[0]'>$dev[1]</option>";
                        }
                    }
                ?>
                
            </select><br><br>
            
            <label for="price">Game Price</label><br>
            <input type="float" name="Price" required><br><br>
            
            <label for="genre">Game Genre</label><br>
                <select name="Category" class="form-select" required>
                    <option value="" disabled selected>Select Game Category</option>
                    <option value="1">Action</option>
                    <option value="2">Adventure</option>
                    <option value="3">RPG</option>
                    <option value="4">Simulation</option>
                    <option value="5">Strategy   </option>
                </select>
                <br><br>    
            <label for="game_image">Game Image</label>
            <input type="file" id="inputFile" class="file-upload" name="GameImage" placeholder="Upload" accept="image/png, image/jpeg" required><br>
            
            <label for="game_image">Game Background</label>
            <input type="file" id="inputFile" class="file-upload" name="GameBackground" placeholder="Upload" accept="image/png, image/jpeg" required><br>

            <label for="game_image">Game Screenshots</label>
            <input type="file" id="inputFile" class="file-upload" name="Screenshot1" style="margin-bottom: 15px;" placeholder="Upload Screenshot 1" accept="image/png, image/jpeg" required>
            <input type="file" id="inputFile" class="file-upload" name="Screenshot2" style="margin-bottom: 15px;" placeholder="Upload Screenshot 2" accept="image/png, image/jpeg" required>
            <input type="file" id="inputFile" class="file-upload" name="Screenshot3" placeholder="Upload Screenshot 3" accept="image/png, image/jpeg" required>
            <br>

            <label for="game_desc">Game Description</label>
            <textarea name="Game_Desc" rows="4" placeholder="Write description here..." required></textarea><br><br>
            
            <input type="submit" name='Add' class="submit-button"><br>
            <a href="" style="margin-top: 10px; color: white; text-decoration: none;">Cancel</a>
        </form>
    </div>
</body>
</html>