<?php

require_once 'settings.php';
require_once 'messages.php';

// Login Page
if (!isset($_SESSION['logged_in'])) {
	$main = "<form method='POST' action='manager.php'>
							<label for='username'>Username:</label>
							<input type='text' id='username' name='username' autofocus><br>
							<label for='password'>Password:</label>
							<input type='password' id='password' name='password'><br>
							<button type='submit' name='login' value='true'>Login</button>
						</form>";
}

if (isset($_GET['logout'])) {
	session_destroy();
	header('location:manager.php');
}

if (isset($_POST['login'])) {
	$sql = "SELECT * FROM tbl_users WHERE `str_username` = '" . $conn->real_escape_string(htmlspecialchars($_POST['username'])) . "';";
	$login = $conn->query($sql)->fetch_assoc();
	
	if ($login['str_username'] == $_POST['username'] && $login['str_password'] == md5($_POST['password'])) {
    $_SESSION['logged_in'] = $_POST['username'];
		header('location:manager.php');
  } else {
		header('location:manager.php?error=2');
	}
}


//default search selection
if (!isset($_GET['sort_records'])) {
	$_GET['sort_records'] = 'tbl_records.str_last';
}

if (isset($_GET['sql'])) {
	
	//when deleting a container
	if ($_GET['sql'] == "delete_container") {
		
		//find parent of deleted container
		$sql = "SELECT int_parent FROM tbl_containers WHERE lng_id = " . $_GET['container_id'] . ";";
		$container = $conn->query($sql)->fetch_assoc();
		
		//change parent of children containers
		$sql = "UPDATE `tbl_records` SET `lng_container_id`=" . $container['int_parent'] . " WHERE lng_container_id = " . $_GET['container_id'] . ";";
		$conn->query($sql);
		
		//change parent of children records
		$sql = "UPDATE `tbl_containers` SET `int_parent`=" . $container['int_parent'] . " WHERE int_parent = " . $_GET['container_id'] . ";";
		$conn->query($sql);
		//delete the container
		$sql = "DELETE FROM `tbl_containers` WHERE lng_id = " . $_GET['container_id'] . ";";
		$conn->query($sql);
		header('location:manager.php?saved=4');
	}
		
	//#########################################################################################################
	
	//delete user
	if ($_GET['sql'] == 'delete_user') {
		//delete the user
		$sql = "DELETE FROM `tbl_users` WHERE lng_id = " . $_GET['user_id'] . ";";
		$conn->query($sql);
		header('location:manager.php?saved=6');
	}
	
	//#########################################################################################################
	
	//when deleting a record
	if ($_GET['sql'] == "delete_record") {
		$sql = "DELETE FROM `tbl_records` WHERE lng_id = " . $_GET['record_id'] . ";";
		$conn->query($sql);
		if ($_GET['record_pic'] != "stock.jpg") {
			unlink($_GET['record_pic']);
		}
		header('location:manager.php?saved=3');
	}
				
	//#########################################################################################################
}

//check to write changes to database
if(isset($_POST['sql'])) {
	
	//when saving edits to a record
	if ($_POST['sql'] == "edit_record") {
			$record_id = $_POST['record_id'];
			if (!$_FILES['record_pic']['error'] == 4) {
					$imageFileType = strtolower(pathinfo(basename($_FILES["record_pic"]["name"]),PATHINFO_EXTENSION));
					$target_dir = "uploads/";
					$target_file = $target_dir . "upload." . $imageFileType;
					$uploadOk = 1;
					// Check if image file is a actual image or fake image
							$check = getimagesize($_FILES["record_pic"]["tmp_name"]);
							if($check !== false) {
									//echo "File is an image - " . $check["mime"] . ".";
									$uploadOk = 1;
							} else {
									$image_error = 3;
									$uploadOk = 0;
							}
					// Check if file already exists
					if (file_exists($target_file)) {
							$image_error = 5;
							$uploadOk = 0;
					}
					// Check file size
					if ($_FILES["record_pic"]["size"] > 500000000) {
							$image_error = 4;
							$uploadOk = 0;
					}
					// Allow certain file formats
					if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
					&& $imageFileType != "gif" ) {
							$image_error = 3;
							$uploadOk = 0;
					}
					// Check if $uploadOk is set to 0 by an error
					if ($uploadOk == 0) {
							header('location:manager.php?error=' . $image_error);
							exit();
					// if everything is ok, try to upload file
					} else {
							if (move_uploaded_file($_FILES["record_pic"]["tmp_name"], $target_file)) {
									$final_file = $target_dir . $record_id . "." . $imageFileType;
									rename($target_file, $final_file);
									if ($imageFileType == 'jpg' || $imageFileType == 'jpeg') {
							            $img = resize_jpg($final_file, 960, 540);
							            imagejpeg($img, $final_file);
						            }
						            if ($imageFileType == 'png') {
							            $img = resize_png($final_file, 960, 540);
							            imagepng($img, $final_file);
						            }
									$sql = "UPDATE `tbl_records` SET `str_first`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_first']))) . "',`str_last`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_last']))) . "',`str_year`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_year']))) . "',`str_desc`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_desc']))) . "',`lng_container_id`=" . $_POST['record_parent'] . ",`str_pic`='" . $final_file . "' WHERE `lng_id` = " . $_POST['record_id'] . ";";
									$conn->query($sql);
									header('location:manager.php?saved=2');
							} else {
									header('location:manager.php?error=0');
							}
					}
			} else {
				$sql = "UPDATE `tbl_records` SET `str_first`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_first']))) . "',`str_last`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_last']))) . "',`str_year`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_year']))) . "',`str_desc`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_desc']))) . "',`lng_container_id`=" . $_POST['record_parent'] . " WHERE `lng_id` = " . $_POST['record_id'] . ";";
				$conn->query($sql);
				header('location:manager.php?saved=2');
			}
	}
	
	//#########################################################################################################
	
	//when saving edits to a container
	if ($_POST['sql'] == "edit_container") {
		$sql = "UPDATE `tbl_containers` SET `str_name`='" . $conn->real_escape_string(htmlspecialchars(trim($_POST['container_name']))) . "',`int_parent`=" . $_POST['container_parent'] . " WHERE lng_id = " . $_POST['container_id'] . ";";
		$conn->query($sql);
		header('location:manager.php?saved=2');
	}
	
	//#########################################################################################################
	
	//when saving edits to a user
	if ($_POST['sql'] == "edit_user") {
		$sql = "UPDATE `tbl_users` SET `str_password`='" . $conn->real_escape_string(md5($_POST['password'])) . "' WHERE lng_id = " . $_POST['user_id'] . ";";
		$conn->query($sql);
		header('location:manager.php?saved=7');
	}
	
	//#########################################################################################################
	
	//when creating a container
	if ($_POST['sql'] == "new_container") {
		$sql = "INSERT INTO `tbl_containers`(`str_name`, `int_parent`) VALUES ('" . $conn->real_escape_string(htmlspecialchars(trim($_POST['container_name']))) . "'," . $_POST['container_parent'] . ");";
		$conn->query($sql);
		header('location:manager.php?saved=0');
	}
	
	//#########################################################################################################
	
	//when creating a user
	if ($_POST['sql'] == "create_user") {
		$sql = "INSERT INTO `tbl_users`(`str_username`, `str_password`) VALUES ('" . $conn->real_escape_string(htmlspecialchars($_POST['username'])) . "','" . $conn->real_escape_string(md5($_POST['password'])) . "');";
		$conn->query($sql);
		header('location:manager.php?saved=5');
	}
	
	//#########################################################################################################
	
	//when creating a record
	if ($_POST['sql'] == "new_record") {
		$record_id = time();
			if (!$_FILES['record_pic']['error'] == 4) {
				$imageFileType = strtolower(pathinfo(basename($_FILES["record_pic"]["name"]),PATHINFO_EXTENSION));
				$target_dir = "uploads/";
				$target_file = $target_dir . "upload." . $imageFileType;
				$uploadOk = 1;
				// Check if image file is a actual image or fake image
						$check = getimagesize($_FILES["record_pic"]["tmp_name"]);
						if($check !== false) {
								echo "File is an image - " . $check["mime"] . ".";
								$uploadOk = 1;
						} else {
									$image_error = 3;
									$uploadOk = 0;
							}
					// Check if file already exists
					if (file_exists($target_file)) {
							$image_error = 5;
							$uploadOk = 0;
					}
					// Check file size
					if ($_FILES["record_pic"]["size"] > 500000000) {
							$image_error = 4;
							$uploadOk = 0;
					}
					// Allow certain file formats
					if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
					&& $imageFileType != "gif" ) {
							$image_error = 3;
							$uploadOk = 0;
					}
					// Check if $uploadOk is set to 0 by an error
					if ($uploadOk == 0) {
							header('location:manager.php?error=' . $image_error);
							exit();
				// if everything is ok, try to upload file
				} else {
						if (move_uploaded_file($_FILES["record_pic"]["tmp_name"], $target_file)) {
								$final_file = $target_dir . $record_id . "." . $imageFileType;
								rename($target_file, $final_file);
								if ($imageFileType == 'jpg' || $imageFileType == 'jpeg') {
							        $img = resize_jpg($final_file, 960, 540);
							        imagejpeg($img, $final_file);
						        }
						        if ($imageFileType == 'png') {
							        $img = resize_png($final_file, 960, 540);
							        imagepng($img, $final_file);
						        }
								$sql = "INSERT INTO `tbl_records`(`lng_id`, `str_first`, `str_last`, `str_pic`, `str_year`, `str_desc`, `lng_container_id`) VALUES ('" . $record_id . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_first']))) . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_last']))) . "','" . $final_file . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_year']))) . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_desc']))) . "'," . $_POST['record_parent'] . ");";
								$conn->query($sql);
								header('location:manager.php?saved=1');
						} else {
								header('location:manager.php?error=0');
						}
				}
			} else {
				$sql = "INSERT INTO `tbl_records`(`lng_id`, `str_first`, `str_last`, `str_pic`, `str_year`, `str_desc`, `lng_container_id`) VALUES ('" . $record_id . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_first']))) . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_last']))) . "','stock.jpg','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_year']))) . "','" . $conn->real_escape_string(htmlspecialchars(trim($_POST['record_desc']))) . "'," . $_POST['record_parent'] . ");";
				$conn->query($sql);
				header('location:manager.php?saved=1');
			}
	}
}

//check to change the main body to an edit/confirmation page
if(isset($_GET['action'])) {
	
	//#########################################################################################################
	
	//for when edit is clicked on a record
	if ($_GET['action'] == "edit_record") {
		
		//pull current information on record
		$sql = "SELECT * FROM tbl_records WHERE lng_id = " . $_GET['record_id'] . ";";
		$record = $conn->query($sql)->fetch_assoc();
		
		//build parent selection list for editor
		$sql = "SELECT * FROM tbl_containers;";
		$parents_list = $conn->query($sql);
		$select = "";
		while ($row = $parents_list->fetch_assoc()) {
			if ($row['lng_id'] == $record['lng_container_id']) {
				$current_parent = " selected";
			} else {
				$current_parent = "";
			}
			$select = $select . '<option value="' . $row['lng_id'] . '"' . $current_parent . '>' . $row['str_name'] . '</option>';
		}
		$main = '<a href="manager.php">Go Back</a><h2>Edit Record: ' . $record['str_first'] . ' ' . $record['str_last'] . '</h2>
						<form method="POST" action="manager.php" enctype="multipart/form-data">
								<label for="record_first">First Name: </label>
								<input name="record_first" id="record_first" value="' . $record['str_first'] . '" maxlength="15" required><br>
								<label for="record_last">Last Name: </label>
								<input name="record_last" id="record_last" value="' . $record['str_last'] . '" maxlength="15" required><br>
								<label for="record_parent">Parent Container: </label>
								<select name="record_parent">
									' . $select . '
								</select><br>
								<label for="record_year">Year: </label>
								<input name="record_year" id="record_year" value="' . $record['str_year'] . '" required><br>
								<div class="upload">
									<label for="record_pic">Picture:</label>
									<input type="file" name="record_pic" id="record_pic"><br>
									<img src="' . $record['str_pic'] . '" alt="' . $record['str_first'] . ' ' . $record['str_last'] . '"><br>
								</div>
								<label for="record_desc">Description: </label>
								<textarea name="record_desc" id="record_desc" maxlength="607">' . $record['str_desc'] . '</textarea>
								<input type="hidden" name="record_id" value="' . $record['lng_id'] . '"><br>
								<button type="submit" name="sql" value="edit_record">Save Changes</button>
							</form>';
	}
	
	//#########################################################################################################
	
	if ($_GET['action'] == "edit_container") {
			//load information about selected container to edit
			$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $_GET['container_id'] . ";";
			$container = $conn->query($sql)->fetch_assoc();

			//build parent selection list for editor
			$sql = "SELECT * FROM tbl_containers WHERE lng_id != " . $container['lng_id'] . ";";
			$parents_list = $conn->query($sql);
			$select = "";
			while ($row = $parents_list->fetch_assoc()) {
				if ($row['lng_id'] == $container['int_parent']) {
					$current_parent = " selected";
				} else {
					$current_parent = "";
				}
				$select = $select . '<option value="' . $row['lng_id'] . '"' . $current_parent . '>' . $row['str_name'] . '</option>';
			}

			//build form for editing container
			$main = '<a href="manager.php">Go Back</a><h2>Edit Container: ' . $container['str_name'] . '</h2>
							<form method="POST">
								<label for="container_name">Name: </label>
								<input type="text" name="container_name" id="container_name" value="' . $container['str_name'] . '" maxlength="40" required><br>
								<label for="container_parent">Parent Container:</label>
								<select name="container_parent" id="container_parent">'
								. $select . 
								'</select><br>
								<input type="hidden" name="container_id" value="' . $container['lng_id'] . '">
								<button type="submit" name="sql" value="edit_container">Save Changes</button>
							</form>';
		}
		
		//#########################################################################################################
		
		
			
		if ($_GET['action'] == "edit_user") {
			//load information about selected user to edit
			$sql = "SELECT * FROM tbl_users WHERE lng_id = " . $_GET['user_id'] . ";";
			$user = $conn->query($sql)->fetch_assoc();
			
			//build form for editing user
			$main = '<a href="manager.php">Go Back</a><h2>Change Password for User: ' . $user['str_username'] . '</h2>
							<form method="POST">
								<label for="password">New Password: </label>
								<input type="password" name="password" id="password" required><br>
								<input type="hidden" name="user_id" value="' . $user['lng_id'] . '">
								<button type="submit" name="sql" value="edit_user">Save Changes</button>
							</form>';
		}
		
		//#########################################################################################################

	if ($_GET['action'] == "new_container") {
			
			//build parent selection list for creator
			$sql = "SELECT * FROM tbl_containers;";
			$parents_list = $conn->query($sql);
			$select = "";
			while ($row = $parents_list->fetch_assoc()) {
				$select = $select . '<option value="' . $row['lng_id'] . '">' . $row['str_name'] . '</option>';
			}

			//build form for creating container
			$main = '<a href="manager.php">Go Back</a><h2>Create Container</h2>
							<form method="POST">
								<label for="container_name">Name: </label>
								<input type="text" name="container_name" id="container_name" maxlength="40" required><br>
								<label for="container_parent">Parent Container:</label>
								<select name="container_parent" id="container_parent">'
								. $select . 
								'</select><br>
								<button type="submit" name="sql" value="new_container">Save Changes</button>
							</form>';
		}
		
		//#########################################################################################################
		
		if ($_GET['action'] == "new_record") {
		
		//build parent selection list for editor
		$sql = "SELECT * FROM tbl_containers;";
		$parents_list = $conn->query($sql);
		$select = "";
		while ($row = $parents_list->fetch_assoc()) {
			$select = $select . '<option value="' . $row['lng_id'] . '">' . $row['str_name'] . '</option>';
		}
		$main = '<a href="manager.php">Go Back</a><h2>Create Record</h2>
						<form method="POST" action="manager.php" enctype="multipart/form-data">
								<label for="record_first">First Name: </label>
								<input name="record_first" id="record_first" maxlength="15" required><br>
								<label for="record_last">Last Name: </label>
								<input name="record_last" id="record_last" maxlength="15" required><br>
								<label for="record_parent">Parent Container: </label>
								<select name="record_parent">
									' . $select . '
								</select><br>
								<label for="record_year">Year: </label>
								<input name="record_year" id="record_year" required><br>
								<label for="record_pic">Picture:</label>
								<input type="file" name="record_pic" id="record_pic"><br>
								<label for="record_desc">Description: </label>
								<textarea name="record_desc" id="record_desc" maxlength="607"></textarea><br>
								<button type="submit" name="sql" value="new_record">Save Changes</button>
							</form>';
	}
	
	//#########################################################################################################
	
	if ($_GET['action'] == 'create_user') {
		$main = '<a href="manager.php">Go Back</a><h2>Create User</h2>
						<form method="POST" action="manager.php">
							<label for="username">Username:</label>
							<input type="text" id="username" name="username"><br>
							<label for="password">Password:</label>
							<input type="password" id="password" name="password"><br>
							<button type="submit" name="sql" value="create_user">Create User</button>
						</form>';
	}
}

if (!isset($main)) {
	
	//get list of records
	$sql = "SELECT tbl_records.str_first AS record_first, tbl_records.str_last AS record_last, tbl_records.str_year AS year, tbl_records.lng_id AS record_id, tbl_containers.str_name AS container_name, tbl_records.str_pic AS record_pic FROM tbl_records INNER JOIN tbl_containers ON tbl_containers.lng_id = tbl_records.lng_container_id ORDER BY " . $_GET['sort_records'] . ";";
	$records = $conn->query($sql);
	//build list of records
	$records_main = '<h2>List of Records</h2>
					<a href="manager.php?action=new_record">Create New Record</a>
					<table>
						<tr>
							<th><a href="manager.php?sort_records=tbl_records.str_first"> First Name</a></th>
							<th><a href="manager.php?sort_records=tbl_records.str_last">Last Name</a></th>
							<th><a href="manager.php?sort_records=tbl_containers.str_name">Container</a></th>
							<th><a href="manager.php?sort_records=tbl_records.str_year">Year</a></th>
							<th>Action</th>
						</tr>';

	while ($row = $records->fetch_assoc()) {
		$records_main = $records_main . '<tr>
											<td>' . $row['record_first'] . '</td>
											<td>' . $row['record_last'] . '</td>
											<td>' . $row['container_name'] . '</td>
											<td>' . $row['year'] . '</td>
											<td><a href="manager.php?action=edit_record&record_id=' . $row['record_id'] . '">edit</a> . . . <a href="manager.php?sql=delete_record&record_id=' . $row['record_id'] . '&record_pic=' . $row['record_pic'] . '" onclick="return confirm(\'Click OK to confirm deletion of record: ' . $row['record_first'] . ' ' . $row['record_last'] . '\');">delete</a></td>
										</tr>';
	}
	
	$records_main = $records_main . '</table>';
	
		
	//get list of containers
	$sql = "SELECT * FROM tbl_containers WHERE lng_id != 0 ORDER BY int_parent;";
	$containers = $conn->query($sql);
	
	//build list of containers
	$containers_main = '<h2>List of Containers</h2>
					<a href="manager.php?action=new_container">Create New Container</a>
					<table>
						<tr>
							<th>Name</th>
							<th>Parent</th>
							<th>Action</th>
						</tr>';

	while ($row = $containers->fetch_assoc()) {
		$container_parent = $conn->query("SELECT * FROM tbl_containers WHERE lng_id = " . $row['int_parent'])->fetch_assoc();
		$containers_main = $containers_main . '<tr>
											<td>' . $row['str_name'] . '</td>
											<td>' . $container_parent['str_name'] . '</td>
											<td><a href="manager.php?action=edit_container&container_id=' . $row['lng_id'] . '">edit</a> . . . <a href="manager.php?sql=delete_container&container_id=' . $row['lng_id'] . '" onclick="return confirm(\'Click OK to confirm deletion of container: ' . $row['str_name'] . '\');">delete</a></td>
										</tr>';
	}
	
	$containers_main = $containers_main . '</table>';
	
	//build list of users
	if ($_SESSION['logged_in'] == "admin") {
		$create_user = '<a href="manager.php?action=create_user">Create New User</a><br>';
		$admin_password = '<a href="manager.php?action=edit_user&user_id=-1">set password</a>';
	} else {
		$create_user = '';
		$admin_password = '';
	}
	$users_main = '<h2>List of Users</h2>
								' . $create_user . '
									<table>
										<tr>
											<th>Username</th>
											<th>Action</th>
										</tr>
										<tr>
											<td>admin</td>
											<td>' . $admin_password . '</td>
										</tr>';
	$sql = "SELECT * FROM tbl_users WHERE str_username != 'admin'";
	$users = $conn->query($sql);
	while ($row = $users->fetch_assoc()) {
		if ($row['str_username'] == $_SESSION['logged_in'] || $_SESSION['logged_in'] == "admin") {
			$change_password = '<a href="manager.php?sql=delete_user&user_id=' . $row['lng_id'] . '">delete</a> . . . <a href="manager.php?action=edit_user&user_id=' . $row['lng_id'] . '">set password</a>';
			} else {
				$change_password = "";
			}
		$users_main = $users_main . '<tr>
																<td>' . $row['str_username'] . '</td>
																<td>' . $change_password . '</td>
															</tr>';
	}
	$users_main = $users_main . "</table>";
	
	//join container and record list together
	$main = "<a href='manager.php?logout=1'>Logout</a>" . $containers_main . $records_main . $users_main;
}

$conn->close();
?>
<html>
	<head>
		<title>Manage Hall of Fame</title>
		<style>
			img {width: 10%; height: auto;}
			.upload {border: 2px solid black; margin-right: auto;}
			table {border-collapse: collapse;}
			table, th, td {border: 1px solid black;}
			#error {background-color: #ff7878;}
			#message {background-color: #eeff8c;}
		</style>
	</head>
	<body>
		<h1>Hall of Fame Management Page</h1>
		<?php if (isset($_GET['error'])) {echo '<h2 id="error">' . $errors[$_GET['error']] . '</h2>';} ?>
		<?php if (isset($_GET['saved'])) {echo '<h2 id="message">' . $saved[$_GET['saved']] . '</h2>';} ?>
		<?php echo $main; ?>
	</body>
</html>