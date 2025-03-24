<?php 

header("Content-Type: application/json");

try {
    // Validate required POST parameters
    if (!isset($_POST['uniqId']) || !isset($_POST['destinationPath']) || !isset($_FILES['myfile'])) {
        throw new Exception("Missing required parameters.");
    }

    // Get the unique identifier and destination path from POST request
    $uniqId = $_POST['uniqId'];
    $destinationPath = $_POST['destinationPath'];

    // Define paths
    $tmpDir = "C:/xampp/htdocs/ewsign/tmp/$uniqId";  // Temporary subfolder
    $serverTmpDir = "C:/ewsign-backend-live-server-master/tmp/$uniqId";  // Server subfolder

    // Ensure directories exist
    if (!is_dir($tmpDir) && !mkdir($tmpDir, 0777, true)) {
        throw new Exception("Failed to create tmp directory.");
    }

    if (!is_dir($serverTmpDir) && !mkdir($serverTmpDir, 0777, true)) {
        throw new Exception("Failed to create server tmp directory.");
    }

    $sourceFile = "$tmpDir/$uniqId.docx";
    $outputFile = "$tmpDir/$uniqId.pdf";

    // Move uploaded DOCX to the tmp directory
    if (!move_uploaded_file($_FILES['myfile']['tmp_name'], $sourceFile)) {
        throw new Exception("Failed to move uploaded DOCX file.");
    }

    // Convert DOCX to PDF using LibreOffice
    $command = "\"C:\\Program Files\\LibreOffice\\program\\soffice.exe\" --headless --convert-to pdf \"$sourceFile\" --outdir \"$tmpDir\"";
    exec($command, $output, $returnVar);

    if ($returnVar !== 0 || !file_exists($outputFile)) {
        throw new Exception("LibreOffice conversion failed.");
    }

    // Move the converted PDF to the server's tmp directory
    $finalPdfPath = "$serverTmpDir/$uniqId.pdf";
    if (!rename($outputFile, $finalPdfPath)) {
        throw new Exception("Failed to move PDF to server directory.");
    }

    // Cleanup: Remove the original DOCX file
    unlink($sourceFile);

    // Respond with the final PDF path
    echo json_encode(["message" => "File converted successfully", "pdfPath" => $finalPdfPath]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
