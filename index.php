<html>
	<head>
		<title>Eine ToDo-Übungsseite</title>
	</head>
	<body>
		<p>
			ToDo - hier könnte ihre Werbung stehen!<br>
			<form method="POST" action="/index.php">
				<!-- http://afilina.com/refresh-form-but-do-not-resubmit-php/ -->
				<label for="filename">Dateiname</label>
				<input type="text" name="filename">
				<input type="submit" name="filesubmit" value="Datei erstellen">
				<!-- <button type="submit" name="filesubmit">Datei erstellen</button> -->
				<p>
					<select name="dateien">
						<option value="">Wählen Sie eine Datei</option>
						<?php
							if($handle = opendir('/var/www/dummy.test/file')){
								$filename = null;
								if (isset($_GET['file']))
									$filename = $_GET['file'];

								while(false !== ($entry = readdir($handle))){
									if($entry !== "." && $entry !== "..") {
										$selected = $filename == $entry ? true : false; 
										echo "<option value=$entry ".($selected ? 'selected' : '').">$entry</option>";
									}
								}
								closedir($handle);
							}
						?>
					</select>
					<input type="submit" name="filedelete" value="Datei löschen">
				</p>
				<p>
					<label for="task">neue Aufgabe</label>
					<input type="text" name="task">
					<input type="text" name="datum" value="dd.mm.yy">
					<input type="text" name="zeit" value="00:00">
					<input type="submit" name="tasksubmit" value="Aufgabe hinzufügen">
				</p>
				<p>
					<input type="submit" name="showtask" value="Aufgaben anzeigen">
				</p>
			</form>
			<?php
				//create file
				if(isset($_POST['filename']) && isset($_POST['filesubmit'])) {
					$filename=$_POST['filename'];
					//execshellarg ?
					if($filename !== ""){
						file_put_contents("/var/www/dummy.test/file/$filename.json", "", FILE_APPEND);
						echo "datei erstellt\n";
						header('Location: index.php');
						exit();
					}
				}
				//delete file
				if(isset($_POST['filename']) && isset($_POST['filedelete'])) {
					$filename=$_POST['dateien'];
					if(unlink("/var/www/dummy.test/file/$filename")){
						echo "datei gelöscht";
						header('Location: index.php');
						exit();
					}else{
						echo "Fehler beim löschen";
					}
				}
				//add task
				if(isset($_POST['tasksubmit']) && ($filename=$_POST['dateien']) != false) {
					//get content
					//$filename = $_POST['dateien'];
					$fileContent = file_get_contents('/var/www/dummy.test/file/'.$filename);
					$tasks = json_decode($fileContent);

					//add content
					$task=$_POST['task'];
					$Datum=$_POST['datum'];
					$zeit=$_POST['zeit'];
					$newTask=array('task'=>$task, 'datum'=>$Datum, 'zeit'=>$zeit);
					// $newTask = ['task' => $task, ...]
					if (is_array($tasks)) {
						$tasks[] = $newTask;
					} else {
						$tasks = [$newTask];
					}
					$json=json_encode($tasks);
					file_put_contents("/var/www/dummy.test/file/$filename", $json);
					
				}
				//delete task
				if (isset($_GET['delete']) && isset($_GET['file'])) {
					$filename = $_GET['file'];
					$id = $_GET['delete'];

					$fileContent = file_get_contents("/var/www/dummy.test/file/".$filename);
					$tasks = json_decode($fileContent, true);

					if (is_array($tasks) && ($id >= 0)) {
						unset($tasks[$id]);
					}

					file_put_contents("/var/www/dummy.test/file/".$filename, json_encode($tasks));
					reset($tasks);
					echo "<ul>";
					foreach ($tasks as $i => $task) {
						echo '<li><a href="?delete='.$i.'&file='.$filename.'">X</a>'
							.$task['task'].' ('.$task['datum'].' '.$task['zeit'].')</li>';
					}
					echo "</ul>";
				}
				//show tasks in file
				if(isset($_POST['showtask']) && ($dataname=$_POST['dateien']) != false) {
					$fileContent = file_get_contents("/var/www/dummy.test/file/$dataname");
					$tasks = json_decode($fileContent, true);
					if (is_array($tasks)) {
						//Todo output to table
						echo "<table>";
						echo "<tr><th></th></tr>";
						foreach ($tasks as $i => $task) {
							echo '<li><a href="?delete='.$i.'&file='.$dataname.'">X</a>'
								.$task['task'].' ('.$task['datum'].' '.$task['zeit'].')</li>';
						}
						echo "</table>";
					}
				}
				//JSON ? Datum ?
			?>
		</p>
	</body>
</html>