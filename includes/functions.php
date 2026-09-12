<?php

function current_language(): string {
    $language = $_SESSION['language'] ?? 'hy';
    return in_array($language, ['hy', 'ru', 'en'], true) ? $language : 'hy';
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['hy', 'ru', 'en'], true)) {
    $_SESSION['language'] = $_GET['lang'];
}

function language_url(string $language): string {
    $query = $_GET;
    $query['lang'] = $language;
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $path . '?' . http_build_query($query);
}

function t(string $text): string {
    static $translations = [
        'hy' => [
            'Дашборд' => 'Վահանակ', 'Сотрудники' => 'Աշխատակիցներ', 'Отделы' => 'Բաժիններ',
            'Посещаемость' => 'Հաճախելիություն', 'Отпуска' => 'Արձակուրդներ', 'Зарплата' => 'Աշխատավարձ',
            'Пользователи' => 'Օգտատերեր', 'Мой профиль' => 'Իմ պրոֆիլը', 'Выйти' => 'Դուրս գալ',
            'Кадры' => 'Անձնակազմ', 'Учёт времени' => 'Ժամանակի հաշվառում', 'Финансы' => 'Ֆինանսներ', 'Система' => 'Համակարգ',
            'Администратор' => 'Ադմինիստրատոր', 'HR-менеджер' => 'HR մենեջեր', 'Руководитель' => 'Ղեկավար', 'Сотрудник' => 'Աշխատակից',
            'Активен' => 'Ակտիվ', 'Неактивен' => 'Ոչ ակտիվ', 'Присутствует' => 'Ներկա', 'Опоздание' => 'Ուշացում',
            'Отсутствует' => 'Բացակա', 'Выходной' => 'Հանգստյան օր', 'На рассмотрении' => 'Դիտարկման փուլում',
            'Одобрено' => 'Հաստատված', 'Отклонено' => 'Մերժված', 'Сегодня' => 'Այսօր', 'Приход' => 'Մուտք', 'Уход' => 'Ելք',
            'Отметить приход' => 'Նշել մուտքը', 'Отметить уход' => 'Նշել ելքը', 'Моя история (последние 14 дней)' => 'Իմ պատմությունը (վերջին 14 օրը)',
            'Команда сегодня' => 'Թիմն այսօր', 'Пока никто не отметился.' => 'Դեռ ոչ ոք չի նշել ներկայությունը։',
            'Записей пока нет.' => 'Գրառումներ դեռ չկան։', 'Сохранить' => 'Պահպանել', 'Отмена' => 'Չեղարկել',
            'Сотрудников' => 'Աշխատակիցներ', 'Отделов' => 'Բաժիններ', 'Заявок на отпуск' => 'Արձակուրդի հայտեր', 'На месте сегодня' => 'Այսօր ներկա',
            'Последние заявки на отпуск' => 'Արձակուրդի վերջին հայտերը', 'Все заявки' => 'Բոլոր հայտերը', 'Сотрудники по отделам' => 'Աշխատակիցներն ըստ բաժինների',
            'Отделы ещё не созданы.' => 'Բաժիններ դեռ չեն ստեղծվել։', 'Имя' => 'Անուն', 'ФИО' => 'Անուն ազգանուն',
            'Должность' => 'Պաշտոն', 'Отдел' => 'Բաժին', 'Роль' => 'Դեր', 'Дата приёма' => 'Աշխատանքի ընդունման ամսաթիվ',
            'Статус' => 'Կարգավիճակ', 'Действия' => 'Գործողություններ', 'Поиск по имени, email, должности...' => 'Փնտրել անունով, էլ․ փոստով կամ պաշտոնով...',
            'Все отделы' => 'Բոլոր բաժինները', 'Найти' => 'Որոնել', 'Сотрудники не найдены.' => 'Աշխատակիցներ չեն գտնվել։',
            'Добавить сотрудника' => 'Ավելացնել աշխատակից', 'Изменить' => 'Փոփոխել', 'Удалить' => 'Ջնջել',
            'Новый сотрудник' => 'Նոր աշխատակից', 'Пароль' => 'Գաղտնաբառ', 'Телефон' => 'Հեռախոս', 'Дата приёма' => 'Աշխատանքի ընդունման ամսաթիվ',
            'Оклад (֏)' => 'Աշխատավարձ (֏)', '— выбрать —' => '— ընտրել —', '— без отдела —' => '— առանց բաժնի —',
            'Редактировать сотрудника' => 'Փոփոխել աշխատակցին', 'Редактирование: ' => 'Փոփոխում՝ ', 'Новый пароль' => 'Նոր գաղտնաբառ',
            'Статус' => 'Կարգավիճակ', 'Отдел добавлен.' => 'Բաժինը ավելացվել է։', 'Отдел обновлён.' => 'Բաժինը թարմացվել է։',
            'Отдел удалён. Сотрудники этого отдела перенесены в «без отдела».' => 'Բաժինը ջնջվել է։ Աշխատակիցները տեղափոխվել են «առանց բաժնի»։',
            'Название' => 'Անվանում', 'Описание' => 'Նկարագրություն', 'Сотрудников' => 'Աշխատակիցներ', 'Новый отдел' => 'Նոր բաժին', 'Добавить отдел' => 'Ավելացնել բաժին',
            'Заявки на отпуск' => 'Արձակուրդի հայտեր', 'Заявки' => 'Հայտեր', 'Новая заявка' => 'Նոր հայտ', 'Тип' => 'Տեսակ', 'Период' => 'Ժամկետ',
            'Причина' => 'Պատճառ', 'Одобрить' => 'Հաստատել', 'Отклонить' => 'Մերժել', 'Рассмотрено' => 'Դիտարկված է',
            'Новая заявка на отпуск' => 'Արձակուրդի նոր հայտ', 'Выберите тип отпуска.' => 'Ընտրեք արձակուրդի տեսակը։',
            'Укажите даты начала и окончания.' => 'Նշեք սկսվելու և ավարտվելու ամսաթվերը։', 'Причина' => 'Պատճառ', 'Необязательно' => 'Ոչ պարտադիր',
            'Отправить заявку' => 'Ուղարկել հայտը', 'Зарплата' => 'Աշխատավարձ', 'Январь' => 'Հունվար', 'Февраль' => 'Փետրվար', 'Март' => 'Մարտ',
            'Апрель' => 'Ապրիլ', 'Май' => 'Մայիս', 'Июнь' => 'Հունիս', 'Июль' => 'Հուլիս', 'Август' => 'Օգոստոս', 'Сентябрь' => 'Սեպտեմբեր',
            'Октябрь' => 'Հոկտեմբեր', 'Ноябрь' => 'Նոյեմբեր', 'Декабрь' => 'Դեկտեմբեր', 'Месяц' => 'Ամիս', 'Год' => 'Տարի',
            'Начислить всем активным сотрудникам' => 'Հաշվարկել բոլոր ակտիվ աշխատակիցների համար', 'Оклад' => 'Հիմնական աշխատավարձ',
            'Бонус' => 'Բոնուս', 'Удержания' => 'Պահումներ', 'Итого' => 'Ընդամենը', 'Корректировать' => 'Կարգավորել', 'Итого к выплате:' => 'Վճարման ենթակա ընդհանուր գումար՝',
            'Пользователи системы' => 'Համակարգի օգտատերեր', 'Текущая роль' => 'Ընթացիկ դեր', 'Изменить роль' => 'Փոխել դերը',
            'Мои данные' => 'Իմ տվյալները', 'Email' => 'Էլ․ փոստ', 'Текущий пароль' => 'Ընթացիկ գաղտնաբառ', 'Новый пароль' => 'Նոր գաղտնաբառ',
            'Сохранить изменения' => 'Պահպանել փոփոխությունները', 'Вход' => 'Մուտք', 'Войти' => 'Մուտք գործել', 'Язык' => 'Լեզու',
            'Сотрудник' => 'Աշխատակից', 'Список сотрудников' => 'Աշխատակիցների ցանկ', 'Дата' => 'Ամսաթիվ',
            'Заявок пока нет.' => 'Հայտեր դեռ չկան։', 'Поиск по имени, email, должности...' => 'Փնտրել անունով, էլ․ փոստով կամ պաշտոնով...',
            'Сотрудники не найдены.' => 'Աշխատակիցներ չեն գտնվել։', 'Укажите ФИО.' => 'Նշեք անուն ազգանունը։',
            'Укажите корректный email.' => 'Նշեք ճիշտ էլ․ փոստ։', 'Пароль должен содержать минимум 6 символов.' => 'Գաղտնաբառը պետք է պարունակի առնվազն 6 նիշ։',
            'Выберите роль.' => 'Ընտրեք դերը։', 'Пользователь с таким email уже существует.' => 'Այս էլ․ փոստով օգտատերն արդեն գոյություն ունի։',
            'Сотрудник успешно добавлен.' => 'Աշխատակիցը հաջողությամբ ավելացվել է։', 'Сотрудник не найден.' => 'Աշխատակիցը չի գտնվել։',
            'Этот email уже занят другим пользователем.' => 'Այս էլ․ փոստն արդեն օգտագործվում է։', 'Новый пароль должен содержать минимум 6 символов.' => 'Նոր գաղտնաբառը պետք է պարունակի առնվազն 6 նիշ։',
            'Данные сотрудника обновлены.' => 'Աշխատակցի տվյալները թարմացվել են։', 'Оставьте пустым, чтобы не менять' => 'Թողեք դատարկ՝ չփոխելու համար',
            'Ежегодный отпуск' => 'Ամենամյա արձակուրդ', 'Больничный' => 'Հիվանդության արձակուրդ', 'Без сохранения оплаты' => 'Չվճարվող արձակուրդ', 'Декретный отпуск' => 'Մայրության արձակուրդ',
            'Управление персоналом' => 'Անձնակազմի կառավարում', 'Инженерная команда' => 'Ինժեներական թիմ', 'Продвижение и бренд' => 'Առաջխաղացում և բրենդ', 'Работа с клиентами' => 'Աշխատանք հաճախորդների հետ',
            'Семейный отпуск' => 'Ընտանեկան արձակուրդ', 'Плохое самочувствие' => 'Վատ ինքնազգացողություն', 'Поездка' => 'Ուղևորություն',
        ],
        'en' => [
            'Дашборд' => 'Dashboard', 'Сотрудники' => 'Employees', 'Отделы' => 'Departments', 'Посещаемость' => 'Attendance', 'Отпуска' => 'Leave', 'Зарплата' => 'Payroll', 'Пользователи' => 'Users', 'Мой профиль' => 'My profile', 'Выйти' => 'Log out', 'Кадры' => 'People', 'Учёт времени' => 'Time tracking', 'Финансы' => 'Finance', 'Система' => 'System', 'Администратор' => 'Administrator', 'HR-менеджер' => 'HR manager', 'Руководитель' => 'Manager', 'Сотрудник' => 'Employee', 'Активен' => 'Active', 'Неактивен' => 'Inactive', 'Присутствует' => 'Present', 'Опоздание' => 'Late', 'Отсутствует' => 'Absent', 'Выходной' => 'Day off', 'На рассмотрении' => 'Pending', 'Одобрено' => 'Approved', 'Отклонено' => 'Rejected', 'Сегодня' => 'Today', 'Приход' => 'Check in', 'Уход' => 'Check out', 'Отметить приход' => 'Check in', 'Отметить уход' => 'Check out', 'Моя история (последние 14 дней)' => 'My history (last 14 days)', 'Команда сегодня' => 'Team today', 'Пока никто не отметился.' => 'No one has checked in yet.', 'Записей пока нет.' => 'No records yet.', 'Сохранить' => 'Save', 'Отмена' => 'Cancel', 'Сотрудников' => 'Employees', 'Отделов' => 'Departments', 'Заявок на отпуск' => 'Leave requests', 'На месте сегодня' => 'Present today', 'Последние заявки на отпуск' => 'Recent leave requests', 'Все заявки' => 'All requests', 'Сотрудники по отделам' => 'Employees by department', 'Отделы ещё не созданы.' => 'No departments yet.', 'Имя' => 'Name', 'ФИО' => 'Full name', 'Должность' => 'Position', 'Отдел' => 'Department', 'Роль' => 'Role', 'Дата приёма' => 'Hire date', 'Статус' => 'Status', 'Действия' => 'Actions', 'Поиск по имени, email, должности...' => 'Search by name, email, position...', 'Все отделы' => 'All departments', 'Найти' => 'Search', 'Сотрудники не найдены.' => 'No employees found.', 'Добавить сотрудника' => 'Add employee', 'Изменить' => 'Edit', 'Удалить' => 'Delete', 'Новый сотрудник' => 'New employee', 'Пароль' => 'Password', 'Телефон' => 'Phone', 'Оклад (֏)' => 'Salary (֏)', '— выбрать —' => '— select —', '— без отдела —' => '— no department —', 'Редактировать сотрудника' => 'Edit employee', 'Редактирование: ' => 'Editing: ', 'Новый пароль' => 'New password', 'Название' => 'Name', 'Описание' => 'Description', 'Новый отдел' => 'New department', 'Добавить отдел' => 'Add department', 'Заявки на отпуск' => 'Leave requests', 'Заявки' => 'Requests', 'Новая заявка' => 'New request', 'Тип' => 'Type', 'Период' => 'Period', 'Причина' => 'Reason', 'Одобрить' => 'Approve', 'Отклонить' => 'Reject', 'Рассмотрено' => 'Reviewed', 'Новая заявка на отпуск' => 'New leave request', 'Выберите тип отпуска.' => 'Select a leave type.', 'Укажите даты начала и окончания.' => 'Enter start and end dates.', 'Необязательно' => 'Optional', 'Отправить заявку' => 'Submit request', 'Ежегодный отпуск' => 'Annual leave', 'Больничный' => 'Sick leave', 'Без сохранения оплаты' => 'Unpaid leave', 'Декретный отпуск' => 'Maternity leave', 'Январь' => 'January', 'Февраль' => 'February', 'Март' => 'March', 'Апрель' => 'April', 'Май' => 'May', 'Июнь' => 'June', 'Июль' => 'July', 'Август' => 'August', 'Сентябрь' => 'September', 'Октябрь' => 'October', 'Ноябрь' => 'November', 'Декабрь' => 'December', 'Месяц' => 'Month', 'Год' => 'Year', 'Начислить всем активным сотрудникам' => 'Generate for all active employees', 'Оклад' => 'Base salary', 'Бонус' => 'Bonus', 'Удержания' => 'Deductions', 'Итого' => 'Total', 'Корректировать' => 'Adjust', 'Итого к выплате:' => 'Total payout:', 'Пользователи системы' => 'System users', 'Текущая роль' => 'Current role', 'Изменить роль' => 'Change role', 'Мои данные' => 'My details', 'Email' => 'Email', 'Текущий пароль' => 'Current password', 'Сохранить изменения' => 'Save changes', 'Вход' => 'Sign in', 'Войти' => 'Sign in', 'Язык' => 'Language', 'Дата' => 'Date', 'Тип отпуска' => 'Leave type', 'Дата начала' => 'Start date', 'Дата окончания' => 'End date', 'дн./год' => 'days/year', 'Начислить зарплату за период' => 'Generate payroll for period', 'Начислений за этот период ещё нет.' => 'No payroll records for this period yet.'
        ],
    ];
    $extra = [
        'hy' => [
            'Тип отпуска' => 'Արձակուրդի տեսակ', 'Дата начала' => 'Սկսվելու ամսաթիվ', 'Дата окончания' => 'Ավարտվելու ամսաթիվ', 'дн./год' => 'օր/տարի',
            'Введите код с картинки' => 'Մուտքագրեք նկարի կոդը', 'Код безопасности' => 'Անվտանգության կոդ', 'Проверка безопасности не пройдена. Обновите код и попробуйте снова.' => 'Անվտանգության ստուգումը չանցավ։ Թարմացրեք կոդը և կրկին փորձեք։',
        ],
        'en' => [
            'Тип отпуска' => 'Leave type', 'Дата начала' => 'Start date', 'Дата окончания' => 'End date', 'дн./год' => 'days/year',
            'Введите код с картинки' => 'Enter the code from the image', 'Код безопасности' => 'Security code', 'Проверка безопасности не пройдена. Обновите код и попробуйте снова.' => 'Security check failed. Refresh the code and try again.',
            'Управление персоналом' => 'Human resources', 'Инженерная команда' => 'Engineering team', 'Продвижение и бренд' => 'Marketing and brand', 'Работа с клиентами' => 'Customer relations',
            'Семейный отпуск' => 'Family leave', 'Плохое самочувствие' => 'Feeling unwell', 'Поездка' => 'Trip',
        ],
    ];
    return $extra[current_language()][$text] ?? ($translations[current_language()][$text] ?? $text);
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function captcha_code(): string {
    if (empty($_SESSION['captcha_hash']) || (int)($_SESSION['captcha_created'] ?? 0) < time() - 300) {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($index = 0; $index < 5; $index++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $_SESSION['captcha_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $_SESSION['captcha_created'] = time();
        $_SESSION['captcha_display'] = $code;
    }
    return (string)$_SESSION['captcha_display'];
}

function verify_captcha(string $answer): bool {
    $hash = $_SESSION['captcha_hash'] ?? '';
    $created = (int)($_SESSION['captcha_created'] ?? 0);
    $valid = $hash !== '' && $created >= time() - 300 && password_verify(strtoupper(trim($answer)), $hash);
    unset($_SESSION['captcha_hash'], $_SESSION['captcha_created'], $_SESSION['captcha_display']);
    return $valid;
}

function login_rate_key(string $email): string {
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '|' . strtolower(trim($email)));
}

function login_rate_limited(string $email): bool {
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hrms_login_' . login_rate_key($email) . '.json';
    if (!is_file($path)) return false;
    $data = json_decode((string)file_get_contents($path), true) ?: [];
    return (int)($data['blocked_until'] ?? 0) > time();
}

function login_rate_failure(string $email): void {
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hrms_login_' . login_rate_key($email) . '.json';
    $handle = fopen($path, 'c+');
    if (!$handle) return;
    flock($handle, LOCK_EX);
    $data = json_decode(stream_get_contents($handle), true) ?: [];
    $now = time();
    $attempts = (int)($data['attempts'] ?? 0);
    if ($now - (int)($data['window_start'] ?? $now) > 900) $attempts = 0;
    $attempts++;
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode(['attempts' => $attempts, 'window_start' => $data['window_start'] ?? $now, 'blocked_until' => $attempts >= 5 ? $now + 900 : 0]));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function login_rate_clear(string $email): void {
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hrms_login_' . login_rate_key($email) . '.json';
    if (is_file($path)) @unlink($path);
}

function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function format_date(?string $date): string {
    if (!$date) return '—';
    return date('d.m.Y', strtotime($date));
}

function format_money($amount): string {
    if ($amount === null) return '—';
    return number_format((float)$amount, 0, '.', ' ') . ' ֏';
}

function role_label(string $role): string {
    $map = [
        'admin' => 'Администратор',
        'hr' => 'HR-менеджер',
        'manager' => 'Руководитель',
        'employee' => 'Сотрудник',
    ];
    return t($map[$role] ?? $role);
}

function status_badge(string $status): string {
    $map = [
        'active' => ['Активен', 'success'],
        'inactive' => ['Неактивен', 'muted'],
        'present' => ['Присутствует', 'success'],
        'late' => ['Опоздание', 'warning'],
        'absent' => ['Отсутствует', 'danger'],
        'day_off' => ['Выходной', 'muted'],
        'pending' => ['На рассмотрении', 'warning'],
        'approved' => ['Одобрено', 'success'],
        'rejected' => ['Отклонено', 'danger'],
    ];
    [$label, $class] = $map[$status] ?? [$status, 'muted'];
    $label = t($label);
    return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
}

/** Fetch all departments as [id => name] for select boxes */
function get_departments_list(PDO $pdo): array {
    $stmt = $pdo->query('SELECT id, name FROM departments ORDER BY name');
    return $stmt->fetchAll();
}

function get_roles_list(PDO $pdo): array {
    return $pdo->query('SELECT id, name FROM roles ORDER BY id')->fetchAll();
}
