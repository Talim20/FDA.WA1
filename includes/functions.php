<?php
/**
 * Common Functions
 * IMSTS - Integrated Market Surveillance Tracking System
 */

require_once 'config/database.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getOperationalYear($date = null) {
    if ($date === null) $date = date('Y-m-d');
    return (int)date('Y', strtotime($date));
}

function getCommunityVisitForYear($communityId, $year) {
    $sql = "SELECT sv.*, vo.outcome_name, t.team_name, t.team_identifier, u.full_name as officer_name
            FROM surveillance_visits sv
            JOIN visit_outcomes vo ON sv.inspection_outcome_id = vo.id
            JOIN teams t ON sv.team_id = t.id
            JOIN users u ON sv.officer_id = u.id
            WHERE sv.community_id = ? AND sv.operational_year = ? AND sv.is_override = FALSE
            ORDER BY sv.visit_date DESC LIMIT 1";
    return fetchOne($sql, [$communityId, $year]);
}

function getCommunityVisitHistory($communityId) {
    $sql = "SELECT sv.*, vo.outcome_name, t.team_name, t.team_identifier, u.full_name as officer_name
            FROM surveillance_visits sv
            JOIN visit_outcomes vo ON sv.inspection_outcome_id = vo.id
            JOIN teams t ON sv.team_id = t.id
            JOIN users u ON sv.officer_id = u.id
            WHERE sv.community_id = ?
            ORDER BY sv.operational_year DESC, sv.visit_date DESC";
    return fetchAll($sql, [$communityId]);
}

function logAudit($userId, $action, $recordType = null, $recordId = null, $description = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $sql = "INSERT INTO audit_log (user_id, action, affected_record_type, affected_record_id, description, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)";
        executeQuery($sql, [$userId, $action, $recordType, $recordId, $description, $ip]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}

function getDashboardStats($year = null) {
    if ($year === null) $year = getOperationalYear();
    
    $stats = [];
    $stats['total_communities'] = fetchOne("SELECT COUNT(*) as count FROM communities WHERE status = 'active'")['count'];
    $stats['visited_this_year'] = fetchOne("SELECT COUNT(DISTINCT community_id) as count FROM surveillance_visits WHERE operational_year = ? AND is_override = FALSE", [$year])['count'];
    $stats['not_visited'] = $stats['total_communities'] - $stats['visited_this_year'];
    $stats['coverage_percentage'] = $stats['total_communities'] > 0 ? round(($stats['visited_this_year'] / $stats['total_communities']) * 100, 1) : 0;
    $stats['total_districts'] = fetchOne("SELECT COUNT(*) as count FROM administrative_areas WHERE type = 'district'")['count'];
    $stats['total_municipalities'] = fetchOne("SELECT COUNT(*) as count FROM administrative_areas WHERE type = 'municipality'")['count'];
    $stats['active_teams'] = fetchOne("SELECT COUNT(*) as count FROM teams WHERE status = 'active'")['count'];
    $stats['total_visits'] = fetchOne("SELECT COUNT(*) as count FROM surveillance_visits WHERE operational_year = ?", [$year])['count'];
    
    return $stats;
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatTime($time) {
    return date('H:i', strtotime($time));
}

function getVisitStatusBadge($communityId, $year = null) {
    if ($year === null) $year = getOperationalYear();
    $visit = getCommunityVisitForYear($communityId, $year);
    if ($visit) {
        return '<span class="badge bg-danger">VISITED — ' . $year . '</span>';
    } else {
        return '<span class="badge bg-success">NOT VISITED — ' . $year . '</span>';
    }
}

function getVisitOutcomes() {
    $sql = "SELECT * FROM visit_outcomes WHERE status = 'active' ORDER BY outcome_name";
    return fetchAll($sql);
}

function getAdministrativeAreas($type = null) {
    $sql = "SELECT * FROM administrative_areas";
    $params = [];
    if ($type) {
        $sql .= " WHERE type = ?";
        $params[] = $type;
    }
    $sql .= " ORDER BY type, name";
    return fetchAll($sql, $params);
}

function getTeams() {
    $sql = "SELECT * FROM teams WHERE status = 'active' ORDER BY team_name";
    return fetchAll($sql);
}

function getUsers() {
    $sql = "SELECT id, full_name, username, email, role, status, created_at FROM users ORDER BY role, full_name";
    return fetchAll($sql);
}

function isDuplicateCommunity($name, $areaId, $excludeId = null) {
    $sql = "SELECT COUNT(*) as count FROM communities WHERE community_name = ? AND administrative_area_id = ?";
    $params = [$name, $areaId];
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    $result = fetchOne($sql, $params);
    return $result['count'] > 0;
}
?>
