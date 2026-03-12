<?php
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PrayerRecordModel.php';

class AppController
{
    private static function resolveStudent(): ?array
    {
        if (is_logged_in_student() && !empty($_SESSION['student'])) {
            return $_SESSION['student'];
        }

        $studentId = (int)($_GET['student_id'] ?? 0);
        if ($studentId > 0) {
            $student = StudentModel::find($studentId);
            if ($student) {
                return [
                    'id' => (int)$student['id'],
                    'name' => $student['name'],
                    'class_id' => (int)$student['class_id'],
                    'class_name' => $student['class_name'],
                    'phone' => $student['phone'],
                ];
            }
        }

        $allStudents = StudentModel::all();
        if (empty($allStudents)) {
            return null;
        }

        $first = $allStudents[0];
        return [
            'id' => (int)$first['id'],
            'name' => $first['name'],
            'class_id' => (int)$first['class_id'],
            'class_name' => $first['class_name'],
            'phone' => $first['phone'],
        ];
    }

    public static function login(): void
    {
        redirect_to(app_url('index.php?page=home'));
    }

    public static function logout(): void
    {
        unset($_SESSION['student_id'], $_SESSION['student']);
        redirect_to(app_url('index.php?page=home'));
    }

    public static function requireStudent(): void
    {
        // Login section removed as requested; app stays accessible without student login.
    }

    public static function home(): void
    {
        self::requireStudent();
        $student = self::resolveStudent();
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $classes = ClassModel::all();
        $today = $student ? PrayerRecordModel::todayForStudent((int)$student['id']) : null;
        $daily = PrayerRecordModel::leaderboard('day', $classId);
        $week = PrayerRecordModel::leaderboard('week', $classId);
        $month = PrayerRecordModel::leaderboard('month', $classId);

        view('layout/header', ['title' => 'Home']);
        view('home/index', compact('student', 'classes', 'classId', 'today', 'daily', 'week', 'month'));
        view('layout/footer');
    }

    public static function tracker(): void
    {
        self::requireStudent();
        $selectedClass = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $search = trim($_GET['search'] ?? '');
        $classes = ClassModel::all();
        $students = StudentModel::all($selectedClass, $search ?: null);

        view('layout/header', ['title' => 'Tracker']);
        view('tracker/index', compact('classes', 'students', 'selectedClass', 'search'));
        view('layout/footer');
    }

    public static function record(): void
    {
        self::requireStudent();
        $studentId = (int)($_GET['student_id'] ?? 0);
        $student = StudentModel::find($studentId);
        if (!$student) {
            redirect_to(app_url('index.php?page=tracker'));
        }

        $message = null;
        $error = null;
        $latest = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'student_id' => $studentId,
                'date' => $_POST['date'] ?? date('Y-m-d'),
                'subah' => isset($_POST['subah']) ? 1 : 0,
                'dhuhr' => isset($_POST['dhuhr']) ? 1 : 0,
                'asr' => isset($_POST['asr']) ? 1 : 0,
                'maghrib' => isset($_POST['maghrib']) ? 1 : 0,
                'isha' => isset($_POST['isha']) ? 1 : 0,
                'salawat' => max(0, (int)($_POST['salawat'] ?? 0)),
            ];

            if (PrayerRecordModel::saveDaily($payload)) {
                $message = 'Record saved successfully.';
                $latest = PrayerRecordModel::todayForStudent($studentId);
            } else {
                $error = 'Duplicate entry: this student already has a record for selected date.';
            }
        }

        view('layout/header', ['title' => 'Prayer Recording']);
        view('tracker/record', compact('student', 'message', 'error', 'latest'));
        view('layout/footer');
    }

    public static function history(): void
    {
        self::requireStudent();
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

        view('layout/header', ['title' => 'History']);
        view('history/index', compact('classes', 'students', 'rows', 'filters'));
        view('layout/footer');
    }

    public static function profile(): void
    {
        self::requireStudent();
        $student = self::resolveStudent();
        view('layout/header', ['title' => 'Profile']);
        view('profile/index', compact('student'));
        view('layout/footer');
    }
}
