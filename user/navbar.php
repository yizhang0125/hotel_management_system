<?php
// Remove the isLoggedIn function from here
?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hotel"></i> Luxury Hotel
        </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                        <a class="nav-link" href="view_bookings.php">
                            <i class="fas fa-book-open"></i> Bookings
                        </a>
            </li>
            <li class="nav-item">
                        <a class="nav-link" href="view_rooms.php">
                            <i class="fas fa-bed"></i> Rooms
                        </a>
            </li>
            <li class="nav-item">
                        <a class="nav-link" href="view_payments.php">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
            </li>
            <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                        </a>
            </li>
            <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
            </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
            </li>
            <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
            </li>
                <?php endif; ?>
        </ul>
        </div>
    </div>
</nav> 