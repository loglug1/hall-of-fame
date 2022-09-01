<?php

//sql connection
require_once 'settings.php';

//when sort method is not set
if (!isset($_GET['sort_by'])) {
	$_GET['sort_by'] = 'str_last';
}

//check page number
if (isset($_GET['page'])) {
	$page_num = (int)$_GET['page'];
} else {
	$page_num = 1;
}

//check container currently open
if (isset($_GET['container'])) {
	$cont_id = (int)$_GET['container'];
} else {
	$cont_id = 0;
}

//set the offset
$offset_records = ($page_num - 1) * 10;
$offset_containers = ($page_num - 1) * 4;

//find all information of current container
$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $cont_id . ";";
$results = $conn->query($sql);
$array = $results->fetch_assoc();
$cont_title = $array['str_name'];
$cont_parent = $array['int_parent'];
$results->free();
$not_home = ($cont_parent != "-1");

//pull all of the objects in current container

	$sql = "SELECT * FROM tbl_records WHERE lng_container_id = " . $cont_id . " ORDER BY " . $_GET['sort_by'] . " LIMIT " . $offset_records . ", 10;";
	$records = $conn->query($sql);

	$sql = "SELECT * FROM tbl_containers WHERE int_parent = " . $cont_id . " ORDER BY str_name LIMIT " . $offset_containers . ", 4;";
	$containers = $conn->query($sql);


//checks if the container is homepage
if ($not_home) {
	$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $cont_parent . ";";
	$parent = $conn->query($sql)->fetch_assoc();
	$breadcrumbs = '<a class="breadcrumb_btn" href="index.php?container=' . $parent['lng_id'] . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $parent['str_name'] . '</a> &gt; <a class="breadcrumb_btn" href="index.php?container=' . $cont_id . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $cont_title . '</a>';

	//build breadcrumbs if not homepage
	while ($parent['int_parent'] != "-1") {
		$sql = "SELECT * FROM tbl_containers WHERE lng_id = " . $parent['int_parent'] . ";";
		$parent = $conn->query($sql)->fetch_assoc();
		$breadcrumbs = '<a class="breadcrumb_btn" href="index.php?container=' . $parent['lng_id'] . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $parent['str_name'] . '</a> &gt; ' . $breadcrumbs;
	}
} else {
	$breadcrumbs = '<a class="breadcrumb_btn" href="index.php?container=' . $cont_id . '&page=1&sort_by=' . $_GET['sort_by'] . '">' . $cont_title . '</a>';
}

//set button to go to parent container
if ($not_home) {
$back = '<p id="back_button" class="top-nav"><a href="index.php?container=' . $cont_parent . '&page=1&sort_by=' . $_GET['sort_by'] . '">&lt; Go Back</a></p>';
}

?>
<html>
	<head>
		<title>Hall of Fame - <?php echo $cont_title;?></title>
		<link rel="stylesheet" href="style.css">
		<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
		<style>
			html {
				background-color: #1a3281;
			}
		</style>
	</head>
	<body>
		<header>
			<h1 class="title" ><?php echo $cont_title;?></h1>
			<p class="breadcrumbs top-nav"><?php echo $breadcrumbs; ?></p>
			<?php
			if ($not_home) {
				echo $back;
			}
			?>
		</header>
		<main>
		<div class="container-container">
			<?php
			//render all containers
			while ($row = $containers->fetch_assoc()) {
					echo '<a href="index.php?page=1&container=' . $row['lng_id'] . '&sort_by=' . $_GET['sort_by'] . '">
									<div class="container">
										<h2>' . $row['str_name'] . '</h2>
									</div>
								</a>';
			}
			?>
		</div>
		<div class="record-container">
			<?php
			//render all records after containers
			while ($row = $records->fetch_assoc()) {
					echo '<a href="record.php?id=' . $row['lng_id'] . '&page=' . $page_num . '&sort_by=' . $_GET['sort_by'] . '">
									<div class="record">
										<h2>' . $row['str_first'] . ' ' . $row['str_last'] . '</h2>
										<img class="icon" src="' . $row['str_pic'] . '" alt="' . $row['str_first'] . ' ' . $row['str_last'] . '">
										<p class="year">Year: ' . $row['str_year'] . '</p>
									</div>
								</a>';
			}
			?>
		</div>
		</main>
		<footer>
			<nav>
				<div id="sort_by">
					<h2>Sort by:</h2>
					<a class="selector<?php if (isset($_GET['sort_by'])) {if ($_GET['sort_by'] == 'str_first') {echo ' selected';}}?>" href="index.php?page=<?php echo $page_num;?>&container=<?php echo $cont_id;?>&sort_by=str_first">First</a>
					<a class="selector<?php if (isset($_GET['sort_by'])) {if ($_GET['sort_by'] == 'str_last') {echo ' selected';}}?>" href="index.php?page=<?php echo $page_num;?>&container=<?php echo $cont_id;?>&sort_by=str_last">Last</a>
					<a class="selector<?php if (isset($_GET['sort_by'])) {if ($_GET['sort_by'] == 'str_year') {echo ' selected';}}?>" href="index.php?page=<?php echo $page_num;?>&container=<?php echo $cont_id;?>&sort_by=str_year">Year</a>
				</div>
				<div id="pages">
					<?php
					if ($page_num > 1) {
						echo '<a class="nav_button" href="index.php?page=' . ($page_num - 1) . '&container=' . $cont_id . '&sort_by=' . $_GET['sort_by'] . '">&lt;</a>';
					}
					?>
					<p class="page_num"> Page: <?php echo $page_num;?> </p>
					<?php
					if ($conn->query("SELECT * FROM tbl_records WHERE lng_container_id = " . $cont_id . " ORDER BY str_year LIMIT " . ($offset_records + 10) . ", 10;")->num_rows || $conn->query("SELECT * FROM tbl_containers WHERE int_parent = " . $cont_id . " ORDER BY str_name LIMIT " . ($offset_containers + 4) . ", 4;")->num_rows) {
						echo '<a class="nav_button" href="index.php?page=' . ($page_num + 1) . '&container=' . $cont_id . '&sort_by=' . $_GET['sort_by'] . '">&gt;</a>';
					}
					?>
				</div>
			</nav>
		</footer>
		<script>
			setTimeout(function () {
			window.location.href = "index.php";
			}, 90000);
		</script>
	</body>
</html>
<?php $conn->close(); ?>
