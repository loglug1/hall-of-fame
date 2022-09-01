<?php

require_once 'settings.php';

//redirect if not properly navigated
if (!isset($_GET['id'])) {
	header('location:index.php?container=0&page=1');
}

//when sort method is not set
if (!isset($_GET['sort_by'])) {
	$_GET['sort_by'] = 'str_last';
}

//pull all information about record
$sql = "SELECT * FROM tbl_records WHERE lng_id = " . $_GET['id'] . ";";
$record = $conn->query($sql)->fetch_assoc();

//pull information about container of record
$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $record['lng_container_id'] . ";";
$container = $conn->query($sql)->fetch_assoc();

$record_name = $record['str_first'] . ' ' . $record['str_last'];

//checks if the container is homepage
$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $record['lng_container_id'] . ";";
$parent = $conn->query($sql)->fetch_assoc();
$breadcrumbs = '<a class="breadcrumb_btn" href="index.php?container=' . $parent['lng_id'] . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $parent['str_name'] . '</a> &gt; <a class="breadcrumb_btn" href="record.php?id=' . $_GET['id'] . '&page=' . $_GET['page'] . '&sort_by=' . $_GET['sort_by'] . '">' . $record_name . '</a>';

//build breadcrumbs
while ($parent['int_parent'] != "-1") {
	$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $parent['int_parent'] . ";";
	$parent = $conn->query($sql)->fetch_assoc();
	$breadcrumbs = '<a class="breadcrumb_btn" href="index.php?container=' . $parent['lng_id'] . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $parent['str_name'] . '</a> &gt; ' . $breadcrumbs;
}

//close connection
$conn->close();
?>
<html>
	<head>
		<title><?php echo $container['str_name'] . " - " . $record_name; ?></title>
		<link rel="stylesheet" href="style.css">
		<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
		<style>
			html {
				background-color: grey;
			}
		</style>
	</head>
	<body>
		<header>
			<h1 class="title"><?php echo $container['str_name'] . ": " . $record_name; ?></h1>
			<p class="breadcrumbs top-nav"><?php echo $breadcrumbs; ?></p>
			<p id="back_button" class="top-nav"><?php echo '<a href="index.php?page=' . $_GET['page'] . '&container=' . $record['lng_container_id'] . '&sort_by=' . $_GET['sort_by'] . '">&lt; Go Back</a>'; ?></p>
		</header>
		<main id="record_main">
			<div id="left">
				<img id="pic" src="<?php if ($record['str_pic'] != "") {echo $record['str_pic'];} else {echo 'placeholder.png';} ?>" alt="<?php echo $record_name; ?>">
				<h2 id="year">Year: <?php echo $record['str_year']; ?></h2>
			</div>
			<div id="right">
				<h2 class="description desc_title">Description:</h2>
				<p class="description"><?php if ($record['str_desc'] != "") {echo $record['str_desc'];} else {echo 'No Description Set';} ?></p>
			</div>
		</main>
		<script>
			setTimeout(function () {
			window.location.href = "index.php";
			}, 90000);
		</script>
	</body>
</html>