<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    .admin-sidebar {
        position: fixed;
        top: 65px;
        left: 0;
        bottom: 0;
        width: 250px;
        background: #284B8C;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        z-index: 1000;
        transition: all 0.3s ease;
        overflow-y: auto;
    }

    .sidebar-menu {
        padding: 20px 0;
    }

    .menu-category {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        padding: 1.5rem 2rem 0.75rem;
    }

    .menu-item {
        padding: 12px 25px;
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
        margin: 4px 0;
    }

    .menu-item:hover, .menu-item.active {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        text-decoration: none;
        border-left-color: #ffffff;
    }

    .menu-item i {
        width: 20px;
        margin-right: 10px;
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .menu-item:hover i,
    .menu-item.active i {
        color: #ffffff;
    }

    .menu-item span {
        font-size: 0.9rem;
        font-weight: 400;
    }

    /* Custom Scrollbar */
    .admin-sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .admin-sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .admin-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .admin-sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Main Content Margin */
    .main-content {
        margin-left: 250px;
        padding: 30px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 0;
        }

        body.show-sidebar .admin-sidebar {
            transform: translateX(0);
        }
    }
</style>

<div class="admin-sidebar">
    <div class="sidebar-menu">
        <div class="menu-category">Main</div>
        <a href="admin_dashboard.php" class="menu-item">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>

        <div class="menu-category">Bookings & Rooms</div>
        <a href="admin_manage_bookings.php" class="menu-item">
            <i class="fas fa-calendar-check"></i>
            Manage Bookings
        </a>
        <a href="rooms.php" class="menu-item">
            <i class="fas fa-bed"></i>
            Manage Rooms
        </a>

        <div class="menu-category">Facilities & Deals</div>
        <a href="admin_facilities.php" class="menu-item">
            <i class="fas fa-swimming-pool"></i>
            Facilities
        </a>
        <a href="best_deals.php" class="menu-item">
            <i class="fas fa-tags"></i>
            Best Deals
        </a>

        <div class="menu-category">Payments</div>
        <a href="admin_view_payments.php" class="menu-item">
            <i class="fas fa-credit-card"></i>
            View Payments
        </a>
    </div>
</div> 