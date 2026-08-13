<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fake a request/auth context minimally isn't needed since dashboard() doesn't use auth().
$controller = new App\Http\Controllers\Registrar\RegistrarController();

$reflection = new ReflectionMethod($controller, 'dashboard');
$view = $reflection->invoke($controller);

$data = $view->getData()['dashboardData'];
echo "activeTermLabel: " . $data['activeTermLabel'] . PHP_EOL;
echo "studentCount: " . $data['studentCount'] . PHP_EOL;
echo "maleCount: " . $data['maleCount'] . PHP_EOL;
echo "femaleCount: " . $data['femaleCount'] . PHP_EOL;
echo "applicantCount: " . $data['applicantCount'] . PHP_EOL;
echo "facultyCount: " . $data['facultyCount'] . PHP_EOL;
echo "departmentCount: " . $data['departmentCount'] . PHP_EOL;
echo "yearLevelData: " . json_encode($data['yearLevelData']) . PHP_EOL;
echo "matrix schoolYear: " . $data['enrollmentProgramSemesterMatrix']['schoolYear'] . PHP_EOL;
echo "matrix semesters: " . json_encode($data['enrollmentProgramSemesterMatrix']['semesters']) . PHP_EOL;
echo "matrix row count: " . count($data['enrollmentProgramSemesterMatrix']['rows']) . PHP_EOL;
foreach (array_slice($data['enrollmentProgramSemesterMatrix']['rows'], 0, 5) as $row) {
    echo "  " . $row['program'] . ' => ' . json_encode($row['values']) . ' total=' . $row['total'] . PHP_EOL;
}
echo "trendValues: " . json_encode($data['trendValues']) . PHP_EOL;
