<?php
require_once __DIR__ . '/db.php';

function rp(float $n): string {
    $neg = $n < 0;
    $s = number_format(abs($n), 0, ',', '.');
    return ($neg ? '-Rp ' : 'Rp ') . $s;
}

function tgl_indo(?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return $hari[(int)date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function fmt_date(string $date): string {
    return date('d/m/Y H:i', strtotime($date));
}

function cash_balance(): float {
    $r = db()->query("SELECT COALESCE(SUM(CASE WHEN type='in' THEN amount ELSE -amount END), 0) FROM cash_transactions")->fetchColumn();
    return (float)$r;
}

function cash_in(float $amount, string $category, string $refType = null, int $refId = null, string $desc = ''): void {
    db()->prepare('INSERT INTO cash_transactions (type, category, amount, reference_type, reference_id, description) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute(['in', $category, $amount, $refType, $refId, $desc]);
}

function cash_out(float $amount, string $category, string $refType = null, int $refId = null, string $desc = ''): void {
    db()->prepare('INSERT INTO cash_transactions (type, category, amount, reference_type, reference_id, description) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute(['out', $category, $amount, $refType, $refId, $desc]);
}
