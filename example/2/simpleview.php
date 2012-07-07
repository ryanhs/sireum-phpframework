<?php
echo 'Tables: ';
echo '<ul>';

while($row = $q->fetch_row())
	echo "<li>{$row['0']}</li>";

echo '</ul>';