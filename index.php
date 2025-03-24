<?php 

// The script uses POST to collect three key pieces of data:
// uniqId: A unique identifier for naming the uploaded file.
// destinationPath: The path where the final output file (PDF) will be moved.
// typeOfFile: The type of the uploaded file, either docx or pdf.

$uniqId = $_POST['uniqId'];
$destinationPath = $_POST['destinationPath'];
$pathToEWSignProject = "C:/ewsign-backend-live-server-master$destinationPath";

$sourceFile = "$uniqId.docx";
$outputFile = "$uniqId.pdf";

try {
    if(move_uploaded_file($_FILES['myfile']['tmp_name'],  $sourceFile)) {
        // Convert the Word document (.docx) to PDF using the 'docto' command-line tool
        // -f: Specifies the input file ($sourceFile), which is the source .docx file
        // -O: Specifies the output file ($outputFile), which will be saved as a .pdf
        // -T: Specifies the target format as PDF (wdFormatPDF)
        exec("docto -f $sourceFile -O $outputFile -T wdFormatPDF");
        unlink($sourceFile);
        exec("move $outputFile $pathToEWSignProject");
    } else {
        throw new Exception("Failed to convert the file to PDF.");
    }
} catch (\Throwable $th) {
    http_response_code(400);
    $response = array("message" => $th->getMessage());
    echo json_encode($response);
    throw new Exception("Failed to convert the file to PDF.");
}