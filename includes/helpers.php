<?php

declare(strict_types=1);

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

//Ashesi email validation (@ashesi.edu.gh)
function ashesi_registration_email_is_valid(string $email): bool
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    }
    $at = strrpos($email, '@');
    if ($at === false) {
        return false;
    }

    return strcasecmp(substr($email, $at), '@ashesi.edu.gh') === 0;
}

//password regex
function registration_password_is_valid(string $password): bool
{
    return preg_match('/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/', $password) === 1;
}

//HTML datetime
function normalize_datetime_local_input(string $input): ?string
{
    $v = str_replace('T', ' ', trim($input));
    if ($v === '') {
        return null;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
        $v .= ':00';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $v)) {
        return null;
    }
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $v);

    return $dt instanceof DateTimeImmutable ? $dt->format('Y-m-d H:i:s') : null;
}

function session_row_is_cancelled(array $row): bool
{
    $c = $row['cancelled_at'] ?? null;

    return $c !== null && $c !== '';
}

//session tags (cancelled | expired | active).
function session_availability_state(array $row): string
{
    if (session_row_is_cancelled($row)) {
        return 'cancelled';
    }

    $tz = new DateTimeZone('Africa/Accra');
    $now = new DateTimeImmutable('now', $tz);

    $start = new DateTimeImmutable((string)$row['start_time'], $tz);
    $end = new DateTimeImmutable((string)$row['end_time'], $tz);

    if ($now < $start) {
        return 'upcoming';
    }

    if ($now >= $start && $now <= $end) {
        return 'ongoing';
    }

    return 'expired';
}

function session_is_full(array $row): bool
{
    return (int)$row['participant_count'] >= (int)$row['capacity'];
}

function format_session_time_range(string $startSql, string $endSql): string
{
    $ts = strtotime($startSql);
    $te = strtotime($endSql);
    if ($ts === false) {
        return '';
    }
    $startLabel = date('M j, Y g:i A', $ts);
    if ($te === false) {
        return $startLabel;
    }
    if (date('Y-m-d', $ts) === date('Y-m-d', $te)) {
        return $startLabel . ' – ' . date('g:i A', $te);
    }

    return $startLabel . ' – ' . date('M j, Y g:i A', $te);
}

function session_status_tag_html(array $row): string
{
    $state = session_availability_state($row);
    $map = [
    'upcoming' => ['cls' => 'session-tag session-tag--upcoming', 'label' => 'Upcoming'],
    'ongoing' => ['cls' => 'session-tag session-tag--active', 'label' => 'Ongoing'],
    'expired' => ['cls' => 'session-tag session-tag--expired', 'label' => 'Expired'],
    'cancelled' => ['cls' => 'session-tag session-tag--cancelled', 'label' => 'Cancelled'],
    ];
    $item = $map[$state];

    return '<span class="' . h($item['cls']) . '">' . h($item['label']) . '</span>';
}
