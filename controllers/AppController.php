<?php
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PrayerRecordModel.php';

class AppController
{
    private static function resolveStudent(): ?array
    {
        $studentId = (int)($_GET['student_id'] ?? $_SESSION['selected_student_id'] ?? 0);
        if ($studentId > 0) {
            $student = StudentModel::find($studentId);
            if ($student) {
                $_SESSION['selected_student_id'] = (int)$student['id'];
                return $student;
            }
        }

        $all = StudentModel::all();
        if (empty($all)) {
            return null;
        }

        $_SESSION['selected_student_id'] = (int)$all[0]['id'];
        return self::resolveStudent();
    }

    public static function login(): void
    {
        redirect_to(app_url('index.php?page=home'));
    }

    public static function logout(): void
    {
        redirect_to(app_url('index.php?page=home'));
    }

    public static function home(): void
    {
        $student = self::resolveStudent();
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $classes = ClassModel::all();
        $today = $student ? PrayerRecordModel::todayForStudent((int)$student['id']) : null;
        $daily = PrayerRecordModel::leaderboard('day', $classId);
        $week = PrayerRecordModel::leaderboard('week', $classId);
        $month = PrayerRecordModel::leaderboard('month', $classId);

        view('layout/header', ['title' => 'ഹോം']);
        view('home/index', compact('student', 'classes', 'classId', 'today', 'daily', 'week', 'month'));
        view('layout/footer');
    }

    public static function tracker(): void
    {
        $selectedClass = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $search = trim($_GET['search'] ?? '');
        $classes = ClassModel::all();
        $students = StudentModel::all($selectedClass, $search ?: null);

        view('layout/header', ['title' => 'ട്രാക്കർ']);
        view('tracker/index', compact('classes', 'students', 'selectedClass', 'search'));
        view('layout/footer');
    }

    public static function record(): void
    {
        $studentId = (int)($_GET['student_id'] ?? 0);
        $student = StudentModel::find($studentId);
        if (!$student) {
            redirect_to(app_url('index.php?page=tracker'));
        }

        $_SESSION['selected_student_id'] = $studentId;
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'student_id' => $studentId,
                'class_id' => (int)$student['class_id'],
                'date' => $_POST['date'] ?? date('Y-m-d'),
                'subah' => ($_POST['subah'] ?? 'missed') === 'completed' ? 1 : 0,
                'dhuhr' => ($_POST['dhuhr'] ?? 'missed') === 'completed' ? 1 : 0,
                'asr' => ($_POST['asr'] ?? 'missed') === 'completed' ? 1 : 0,
                'maghrib' => ($_POST['maghrib'] ?? 'missed') === 'completed' ? 1 : 0,
                'isha' => ($_POST['isha'] ?? 'missed') === 'completed' ? 1 : 0,
                'salawat' => max(0, (int)($_POST['salawat'] ?? 0)),
            ];

            if (PrayerRecordModel::saveDaily($payload)) {
                $message = 'Saved successfully.';
            } else {
                $error = 'Duplicate entry for this student and day.';
            }
        }

        view('layout/header', ['title' => 'രേഖപ്പെടുത്തുക']);
        view('tracker/record', compact('student', 'message', 'error'));
        view('layout/footer');
    }

    public static function history(): void
    {
        $filters = [
            'class_id' => (int)($_GET['class_id'] ?? 0) ?: null,
            'date' => $_GET['date'] ?? null,
            'student_id' => (int)($_GET['student_id'] ?? 0) ?: null,
        ];

        $classes = ClassModel::all();
        $students = StudentModel::all();
        $rows = PrayerRecordModel::history($filters);

        if (($_GET['export'] ?? '') === 'csv') {
            $csv = PrayerRecordModel::exportCsv($filters);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="prayer-history.csv"');
            echo $csv;
            exit;
        }

        view('layout/header', ['title' => 'ഹിസ്റ്ററി']);
        view('history/index', compact('classes', 'students', 'rows', 'filters'));
        view('layout/footer');
    }

    public static function profile(): void
    {
        $student = self::resolveStudent();
        view('layout/header', ['title' => 'പ്രൊഫൈൽ']);
        view('profile/index', compact('student'));
        view('layout/footer');
    }
}
