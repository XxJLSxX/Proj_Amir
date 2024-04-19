<?php
require 'connection.php';

class MoistFunctions {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function sqlExecute($sql) {
        $result = mysqli_query($this->connection, $sql);
        $data = array();
        if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)) {
                $data_row=array();
                foreach($row as $r) {
                    array_push($data_row, $r);
                }
                array_push($data, $data_row);
            }
        }
        return $data;
    }
    
    public function showAll($tbl) {
        $sql = "SELECT * FROM $tbl where email != 'admin@moist.com'";
        return $this->sqlExecute($sql); 
    }
    
    public function showRecords($tbl, $where=null, $join=null, $col1=null, $col2=null, $wherejoin=null, $m_join=null) {
        $sql = "SELECT * FROM $tbl";
        if ($where != null) {
            $sql.= " Where $where";
        }
        if ($join != null) {
            $sql.= " Left Join $join ON $col1 = $col2";
            if ($m_join != null){
                $sql.= " Left Join $join ON $col1 = $col2";
                
            }
            if ($wherejoin != null) {
                $sql.= " Where $wherejoin";
            }
        }
        
        return $this->sqlExecute($sql);
    }

    public function getPaymentMethods() {
        $sql = "SELECT * FROM Payment_Method";
        return $this->sqlExecute($sql);
    }

    public function UserRegistry($data, $selectedPaymentMethodID, $tbl){
        $name = mysqli_real_escape_string($this->connection, $data['name']);
        $username = mysqli_real_escape_string($this->connection, $data['username']);
        $email = mysqli_real_escape_string($this->connection, $data['email']);
        $password = mysqli_real_escape_string($this->connection, $data['password']);

        $sqlInsertUser = "INSERT INTO $tbl(Name, User_Name, Email, Password, PMethod_ID) VALUES ('$name', '$username', '$email', '$password', '$selectedPaymentMethodID')";
        mysqli_query($this->connection, $sqlInsertUser);

        return true;
    }


    public function addQuery($data, $tbl){
        $tbl_columns = implode(",", array_keys($data));
        $tbl_values = implode("','", $data);
        $sql = "INSERT INTO $tbl($tbl_columns) VALUES ('$tbl_values')";
        return mysqli_query($this->connection, $sql);
    }

    public function updateQuery($data,$tbl,$id){
        $update="";
        foreach($data as $key => $value){
            $update.=" $key='$value' ,";
        }
        $update = substr($update,0,-1);
        $primary_key = array_keys($id)[0];
        $key_value = $id[$primary_key];
        $sql = "UPDATE $tbl SET {$update} WHERE $primary_key=$key_value";
        return mysqli_query($this-> connection, $sql);
    }
    

    public function uploadFile($file, $target_dir, $new_file_name) {
        $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $uploadOk = 1;
    
        // Check file size
        if ($file["size"] > 20000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
    
        // Allow certain file formats
        if ($fileExtension != "jpg" && $fileExtension != "jpeg" && $fileExtension != "png") {
            echo "Sorry, only JPG, JPEG, and PNG files are allowed.";
            $uploadOk = 0;
        }else {  // New file name
            $target_file = $target_dir . $new_file_name; // Full path to the new file
        }
    
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                echo "The file ". htmlspecialchars(basename($file["name"])). " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
     public function loginUser($email, $password) {
        $userData = $this->showRecords('Users', "email = '$email'");

        if (count($userData) > 0) {
            $userStatus = $userData[0][0];
            $hashedPassword = $userData[0][4];

            if ($userStatus == '1') {
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['Admin'] = $userStatus;
                    header("Location: ../Admin/");
                } else {
                    return "Incorrect Username or Password";
                }
            } else {
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['User'] = $userStatus;
                    header("Location: index.php");
                } else {
                    return "Incorrect Username or Password";
                }
            }
        } else {
            return "User not found";
        }
    }

/* ------------------------------------------------ Integration Point ------------------------------------------------ */
/* Landing Page Functions */

    public function queryRandomByLimitOrderBy($table, $limit, $orderColumn) {
        $query = "SELECT * FROM (SELECT * FROM $table ORDER BY RAND()) AS random_$table ORDER BY $orderColumn DESC LIMIT $limit";
        return $this->connection->query($query);
    }

    public function paginateItems($table, $limit = 5) {
        // Pagination variables
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page - 1) * $limit;
    
        // Sort by date
        $date_order = "";
        if (isset($_GET['latest']) == true) {
            $date_order = "ORDER BY g.Upload_Date desc"; // Sort by Upload_Date of table g (games)
        }
    
        // Sort by category
        $category_filter = "";
        if (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $category = $_GET['sort'];
            $category_filter = "WHERE g.Category = '$category'"; // Filter by Category of table g (games)
        }
    
        // Fetch items for the current page
        $sql = "SELECT g.*, d.Developer_Name 
                FROM $table g
                INNER JOIN developer d ON g.Developer_ID = d.Developer_ID
                $category_filter
                $date_order
                LIMIT $start, $limit";
        $result = $this->connection->query($sql);
    
        // Fetch total number of items
        $total_items = $this->connection->query("SELECT COUNT(*) AS count FROM $table g $category_filter")->fetch_assoc()['count'];
        $total_pages = ceil($total_items / $limit);
    
        // Pagination links
        $prev_page = max(1, $page - 1);
        $next_page = min($total_pages, $page + 1);
    
        return [
            'items' => $result,
            'total_pages' => $total_pages,
            'prev_page' => $prev_page,
            'next_page' => $next_page,
            'latest' => $date_order,
            'sort' => $category_filter
        ];
    }

    public function paginateDevs($table, $limit = 4) {
        // Pagination variables
        $pageDev = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($pageDev - 1) * $limit;
    
        // Sort by name
        $name_order = "";
        if (isset($_GET['Name_Sort']) == true) {
        $name_order = "ORDER BY Developer_Name desc"; 
        }
    
        // Fetch items for the current page
        $sql = "SELECT * FROM $table g
                INNER JOIN developer d ON g.Developer_ID = d.Developer_ID
                $name_order
                LIMIT $start, $limit";
        $resultDev = $this->connection->query($sql);
    
        // Fetch total number of items
        $total_items = $this->connection->query("SELECT COUNT(*) AS count FROM $table")->fetch_assoc()['count'];
        $total_pagesDev = ceil($total_items / $limit);
    
        // Pagination links
        $prev_pageDev = max(1, $pageDev - 1);
        $next_pageDev = min($total_pagesDev, $pageDev + 1);
    
        return [
            'itemsDev' => $resultDev,
            'total_pagesDev' => $total_pagesDev,
            'prev_pageDev' => $prev_pageDev,
            'next_pageDev' => $next_pageDev,
            'Name_Sort' => $name_order
        ];
    }
    

    public function fetchGameData() {
        $games = [];
        $count = 1;
    
        // Fetch game details from the database
        $sql = "SELECT g.*, d.Developer_Name
                FROM games g
                INNER JOIN developer d ON g.Developer_ID = d.Developer_ID";
        $result = $this->connection->query($sql);
    
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $games["Game $count"] = [
                    'id' => $row['Game_ID'],
                    'title' => $row['Game_Name'],
                    'developer' => $row['Developer_Name'],
                    'genre' => $row['Category'],
                    'description' => strip_tags($row['Game_Desc']),
                    'upload_date' => $row['Upload_Date'],
                    'price' => $row['Price']
                ];
                $count++;
            }
        } else {
            echo "No games found.";
        }
    
        return $games;
    }

    public function getGameInfo($id) {
        $game_info = [];
    
        $sql = "SELECT g.*, d.Developer_Name
                FROM Games g
                INNER JOIN Developer d ON g.Developer_ID = d.Developer_ID
                WHERE g.Game_ID = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            $game_info = $result->fetch_assoc();
        }
    
        return $game_info;
    }
}
