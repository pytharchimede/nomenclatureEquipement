<?php

// This script retrieves all nomenclatures from the database and returns them as a JSON response.
require_once '../model/Nomenclature.php';
header('Content-Type: application/json');
echo json_encode(Nomenclature::getAll());
