<?php
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PrayerRecordModel.php';

class AppController {
    public static function home(): void {
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $classes = ClassModel::all();
        $daily = PrayerRecordModel::leaderboard('day');
        $week = PrayerRecordModel::leaderboard('week');
        $month = PrayerRecordModel::leaderboard('month');
        view('layout/header', ['title' => 'ഹോം']);
        view('home/index', compact('classes', 'classId', 'daily', 'week', 'month'));
        view('layout/footer');
    }

    public static function tracker(): void {
        $selectedClass = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $classes = ClassModel::all();
        $students = StudentModel::all($selectedClass, $search ?: null);
        view('layout/header', ['title' => 'ട്രാക്കർ']);
        view('tracker/index', compact('classes', 'students', 'selectedClass', 'search'));
        view('layout/footer');
    }

    public static function record(): void {
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
        $student = StudentModel::find($studentId);
        if (!$student) {
            redirect_to('index.php?page=tracker');
        }

        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'student_id' => $studentId,
                'class_id' => (int)$student['class_id'],
                'date' => $_POST['date'] ?? date('Y-m-d'),
                'subah' => isset($_POST['subah']) ? 1 : 0,
                'dhuhr' => isset($_POST['dhuhr']) ? 1 : 0,
                'asr' => isset($_POST['asr']) ? 1 : 0,
                'maghrib' => isset($_POST['maghrib']) ? 1 : 0,
                'isha' => isset($_POST['isha']) ? 1 : 0,
                'salawat' => max(0, (int)($_POST['salawat'] ?? 0)),
            ];
            if (PrayerRecordModel::saveDaily($payload)) {
                $message = 'ദിവസത്തിലെ രേഖ വിജയകരമായി സേവ് ചെയ്തു.';
            } else {
                $error = 'ഈ വിദ്യാർത്ഥിക്ക് ഇന്നത്തെ രേഖ ഇതിനകം സേവ് ചെയ്തിട്ടുണ്ട്.';
            }
        }

        view('layout/header', ['title' => 'പ്രാർത്ഥന രേഖ']);
        view('tracker/record', compact('student', 'message', 'error'));
        view('layout/footer');
    }

    public static function history(): void {
        $filters = [
            'class_id' => isset($_GET['class_id']) ? (int)$_GET['class_id'] : null,
            'date' => $_GET['date'] ?? null,
            'student_id' => isset($_GET['student_id']) ? (int)$_GET['student_id'] : null,
        ];
        $classes = ClassModel::all();
        $students = StudentModel::all();
        $rows = PrayerRecordModel::history($filters);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $csv = PrayerRecordModel::exportCsv($filters);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="history.csv"');
            echo $csv;
            exit;
        }

        view('layout/header', ['title' => 'ഹിസ്റ്ററി']);
        view('history/index', compact('classes', 'students', 'rows', 'filters'));
        view('layout/footer');
    }

    public static function profile(): void {
        view('layout/header', ['title' => 'പ്രൊഫൈൽ']);
        view('profile/index');
        view('layout/footer');
    }
}
