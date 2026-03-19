<?php
require_once '../connection/connection.php';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="sample-students.xlsx"');

// Create simple HTML table for Excel
echo "<table border='1'>";
echo "<tr><th>STUDENT_ID</th><th>STUDENT_NAME</th></tr>";
echo "<tr><td>5262140032</td><td>PEPRAH DANIEL</td></tr>";
echo "<tr><td>5262140033</td><td>HEISDANITO</td></tr>";
echo "<tr><td>5262140034</td><td>JOHN DOE</td></tr>";
echo "<tr><td>5262140035</td><td>JANE SMITH</td></tr>";
echo "</table>";
?>