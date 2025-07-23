<header>
    <div class="logo">
        <a href="../../index.php">
            <div>
                <img src="../../image/logo.png" alt="Catfe">
            </div>
        </a>
    </div>
    <nav class="header_menu">
        <ul class="header_menu_list">
            <li class="header_menu_list_element" id="product">
                <a href="../product.php"><img src="../../image/icon_product.png" class="icon">Product</a>
            </li>
            <li class="header_menu_list_element" id="about">
                <a href="../about.php"><img src="../../image/icon_about.png" class="icon">About</a>
            </li>
            <li class="header_menu_list_element" id="testimonial">
                <a href="../testimonial.php"><img src="../../image/icon_testimonial.png" class="icon">Testimonial</a>
            </li>
            <li class="header_menu_list_element" id="profile">
                <a href="../profile.php" class="menu_list_profile">
                    <img src="../../image/<?php echo $now_user_profile_photo; ?>" alt="<?php echo $now_user_name; ?>" class="profile_photo">
                    <p><?php echo $now_user_name; ?></p>
                </a>
            </li>
            <li class="header_menu_list_element" id="list">
                <button onclick="openNav()"><p>&#9776;</p></button>
            </li>
        </ul>
    </nav>
</header>
<div id="sidebar" class="sidebar">
    <div class="sidebar_first">
        <a href="../profile.php" onclick="closeNav()" class="menu_list_profile_icon">
            <img src="../../image/<?php echo $now_user_profile_photo; ?>" alt="<?php echo $now_user_name; ?>">
            <p><?php echo $now_user_name; ?></p>
        </a>
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    </div>
    <a href="../../index.php" onclick="closeNav()"><img src="../../image/icon_list.png" alt="Menu"> Home</a>
    <a href="../product.php" onclick="closeNav()"><img src="../../image/icon_product.png" alt="Product"> Product</a>
    <a href="../about.php" onclick="closeNav()"><img src="../../image/icon_about.png" alt="About"> About</a>
    <a href="../testimonial.php" onclick="closeNav()"><img src="../../image/icon_testimonial.png" alt="Testimonial"> Testimonial</a>
    <a href="../contact_us.php" onclick="closeNav()"><img src="../../image/icon_contact.png" alt="Contact Us"> Contact Us</a>
    <?php if (in_array($now_user_group,['customer','admin'])) {?>
        <a href="./create_appointment_comfirm.php" onclick="closeNav()"><img src="../../image/icon_appointment.png" alt="Appointment"> Appointment</a>
    <?php } ?>
    <?php if (in_array($now_user_group,['staff','admin'])) {?>
        <div class="staff_bar">
            <a href="javascript:void(0)" onclick="staff()" id="staff_bar_outside"><img src="../../image/icon_testimonial.png" alt="staff_bar"> Staff Bar</a>
            <div id="staff_bar_inside">
                <a href="../list_order.php">Order List</a>
                <a href="../list_appointment.php">Appointment List</a>
            </div>
        </div>
    <?php } ?>
    <?php if (in_array($now_user_group,['admin'])) { ?>
        <div class="admin_bar">
            <a href="javascript:void(0)" onclick="admin()" id="admin_bar_outside"><img src="../../image/icon_testimonial.png" alt="admin_bar"> Admin Bar</a>
            <div id="admin_bar_inside">
                <a href="../register.php">Register</a>
                <a href="../list_account.php">Account List</a>
                <a href="../list_order.php">Order List</a>
                <a href="../list_testimonial.php">Testimonial List</a>
                <a href="../list_appointment.php">Appointment List</a>
                <a href="../list_cat.php">Cat List</a>
                <a href="../list_meal.php">Meal List</a>
                <a href="../list_photo.php">Photo List</a>
                <a href="../list_contact.php">Contact List</a>
            </div>
        </div>
        <a></a>
        <a></a>
        <a></a>
        <a></a>
        <a></a>
    <?php } ?>
</div>