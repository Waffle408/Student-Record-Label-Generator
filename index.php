<?php
require('tcpdf.php');

// Function to generate labels on A4 for proper printing
function generateLabels($students) {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(5, 10, 5);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage();
    
    $labelWidth = 99.1;
    $labelHeight = 34;
    $marginX = 5;
    $marginY = 15;
    $gapMiddle = 3;
    $gapBottom = 0;
    $labelsPerRow = 2;
    $labelsPerColumn = 8;
    $count = 0;
    
    foreach ($students as $student) {
        $lastName = strtoupper($student['last_name']);
        $firstThree = strtoupper(substr($lastName, 0, 3)); // Get first three letters
        $fullName = strtoupper($student['first_name'] . " " . $lastName);
        $dob = "DOB: " . $student['dob'];
        $year7 = $student['year7']; // Only the year, no prefix
        
        $x = $marginX + ($count % $labelsPerRow) * ($labelWidth + $gapMiddle);
        $y = $marginY + floor($count / $labelsPerRow) * ($labelHeight + $gapBottom);
        
        if ($y + $labelHeight > 297 - $gapBottom) { // If exceeding A4 height, add new page
            $pdf->AddPage();
            $y = $marginY;
            $count = 0;
        }
        
        // Adjust font size if name is too long
        $fontSize = (strlen($fullName) > 10) ? 10 : 12;
        // $pdf->Rect($x, $y, $labelWidth, $labelHeight);
        // Print first three letters at the top of the label, rotated vertically
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->StartTransform();
        $pdf->Rotate(90, $x + 10, $y + 15);
        $pdf->SetXY($x + 8, $y + 8);
        $pdf->MultiCell(10, 5, implode("\n", str_split($firstThree)), 0, 'C');
        $pdf->StopTransform();
        
        // Print duplicate first three letters below the first set
        $pdf->StartTransform();
        $pdf->Rotate(90, $x + 10, $y + 30);
        $pdf->SetXY($x + 8, $y + 23);
        $pdf->MultiCell(10, 5, implode("\n", str_split($firstThree)), 0, 'C');
        $pdf->StopTransform();
        
        // Print Year 7 at the bottom of the label, rotated vertically
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->StartTransform();
        $pdf->Rotate(90, $x + $labelWidth - 10, $y + $labelHeight - 10);
        $pdf->SetXY($x + $labelWidth - 22, $y + $labelHeight - 10);
        $pdf->MultiCell(20, 6, "$year7", 0, 'C');
        $pdf->StopTransform();
        
        // Print duplicate Year 7 below
        $pdf->StartTransform();
        $pdf->Rotate(90, $x + $labelWidth - 10, $y + ($labelHeight / 2) - 10);
        $pdf->SetXY($x + $labelWidth - 22, $y + ($labelHeight / 2) - 10);
        $pdf->MultiCell(20, 6, "$year7", 0, 'C');
        $pdf->StopTransform();
        
        // Print student name and DOB centered in the label with dynamic font size
        $pdf->SetFont('helvetica', '', $fontSize);
        $pdf->SetXY($x + 20, $y + 4);
        $pdf->MultiCell($labelWidth - 40, 6, "$fullName\n$dob", 0, 'C');
        
        // Print duplicate in lower half for wrapping
        $pdf->SetXY($x + 20, $y + ($labelHeight / 2) + 4);
        $pdf->MultiCell($labelWidth - 40, 6, "$fullName\n$dob", 0, 'C');
        
        $count++;
    }
    
    $pdf->Output('Student_Labels.pdf', 'D');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $students = [];
        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($file); // Skip header
        while ($row = fgetcsv($file)) {
            $students[] = [
                'first_name' => $row[0],
                'last_name' => $row[1],
                'dob' => $row[2],
                'year7' => $row[3]
            ];
        }
        fclose($file);
        generateLabels($students);
    } elseif (!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['dob']) && !empty($_POST['year7'])) {
        generateLabels([
            [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'dob' => $_POST['dob'],
                'year7' => $_POST['year7']
            ]
        ]);
    } else {
        echo "<p>Please provide student information or upload a CSV file.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Label Printer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-right: 20px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        input[type="text"], input[type="date"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Student Label Printer</h2>
    <div class="container">
        <div class="form-container">
            <form method="post" enctype="multipart/form-data">
                <h3>Single Student Entry</h3>
                First Name: <input type="text" name="first_name" required><br>
                Last Name: <input type="text" name="last_name" required><br>
                Date of Birth: <input type="date" name="dob" required><br>
                Year 7 Start: <input type="text" name="year7" required><br>
                <input type="submit" value="Generate Label">
            </form>
            <hr>
            <form method="post" enctype="multipart/form-data">
                <h3>Upload CSV File</h3>
                <p>Format: First Name, Last Name, DOB (YYYY-MM-DD), Year 7 Start</p>
                <input type="file" name="csv_file" accept=".csv" required>
                <input type="submit" value="Generate Labels">
            </form>
        </div>
        <div class="table-container">
            <h3>Grade Level Table</h3>
            <table>
                <tr>
                    <th>Year 7 start year</th>
                    <th>Current Grade</th>
                </tr>
                <script>
                    let currentYear = new Date().getFullYear();
                    for (let i = 0; i < 7; i++) {
                        document.write(`<tr><td>${currentYear - i +1}</td><td>Grade ${6 + i}</td></tr>`);
                    }
                </script>
            </table>
        </div>
    </div>
</body>
</html>