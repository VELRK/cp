<!-- sidebar dashboard -->
<div class="sidebar-menu-dashboard">
    <a href="<?php echo base_url(); ?>" class="logo-box">
        <img src="<?php echo base_url('assets/images/logo/logo-footer@2x.png'); ?>" alt="Coimbatore Properties logo">
    </a>
    <div class="user-box">
        <p class="fw-6">Profile</p>
        <div class="user">
            <div class="icon-box">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_13487_13661)">
                        <path
                            d="M10.0007 9.99947C10.9357 9.99947 11.8496 9.72222 12.627 9.20278C13.4044 8.68334 14.0103 7.94504 14.3681 7.08124C14.7259 6.21745 14.8196 5.26695 14.6372 4.34995C14.4547 3.43295 14.0045 2.59063 13.3434 1.92951C12.6823 1.26839 11.84 0.81816 10.923 0.635757C10.006 0.453354 9.05546 0.54697 8.19166 0.904766C7.32787 1.26256 6.58957 1.86847 6.07013 2.64586C5.55069 3.42326 5.27344 4.33723 5.27344 5.2722C5.27469 6.52556 5.77314 7.72723 6.65941 8.6135C7.54567 9.49976 8.74734 9.99821 10.0007 9.99947ZM10.0007 2.12068C10.624 2.12068 11.2333 2.30551 11.7516 2.65181C12.2699 2.9981 12.6738 3.4903 12.9123 4.06616C13.1509 4.64203 13.2133 5.27569 13.0917 5.88702C12.9701 6.49836 12.6699 7.05991 12.2292 7.50065C11.7884 7.9414 11.2269 8.24155 10.6155 8.36315C10.0042 8.48476 9.37054 8.42235 8.79468 8.18382C8.21881 7.94528 7.72661 7.54135 7.38032 7.02308C7.03403 6.50482 6.8492 5.89551 6.8492 5.2722C6.8492 4.43636 7.18123 3.63476 7.77225 3.04374C8.36328 2.45271 9.16488 2.12068 10.0007 2.12068Z"
                            fill="white" />
                        <path
                            d="M10.0011 11.5762C8.12108 11.5783 6.31869 12.326 4.98934 13.6554C3.65999 14.9847 2.91224 16.7871 2.91016 18.6671C2.91016 18.876 2.99316 19.0764 3.14092 19.2242C3.28868 19.372 3.48908 19.455 3.69803 19.455C3.90699 19.455 4.10739 19.372 4.25515 19.2242C4.4029 19.0764 4.48591 18.876 4.48591 18.6671C4.48591 17.2044 5.06697 15.8016 6.10126 14.7673C7.13555 13.733 8.53835 13.1519 10.0011 13.1519C11.4638 13.1519 12.8666 13.733 13.9009 14.7673C14.9352 15.8016 15.5162 17.2044 15.5162 18.6671C15.5162 18.876 15.5992 19.0764 15.747 19.2242C15.8947 19.372 16.0951 19.455 16.3041 19.455C16.513 19.455 16.7134 19.372 16.8612 19.2242C17.009 19.0764 17.092 18.876 17.092 18.6671C17.0899 16.7871 16.3421 14.9847 15.0128 13.6554C13.6834 12.326 11.881 11.5783 10.0011 11.5762Z"
                            fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_13487_13661">
                            <rect width="18.9091" height="18.9091" fill="white"
                                transform="translate(0.546875 0.544922)" />
                        </clipPath>
                    </defs>
                </svg>
            </div>
            <div class="content">
                <div class="caption-2 text">Account</div>
                <div class="text-white fw-6" style="font-size: 14px; margin-bottom: 4px;">
                    <?php echo isset($userData['userName']) && !empty($userData['userName']) ? htmlspecialchars($userData['userName']) : 'User'; ?>
                </div>
                <div class="text-white" style="font-size: 12px; opacity: 0.9;">
                    <?php echo isset($userData['userEmail']) && !empty($userData['userEmail']) ? htmlspecialchars($userData['userEmail']) : 'No email'; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="menu-box">
        <div class="title fw-6">Menu</div>
        <ul class="box-menu-dashboard">

            <li class="nav-menu-item <?php echo (current_url() == base_url('dashboard/enquiries')) ? 'active' : ''; ?>">
                <a class="nav-menu-link" href="<?php echo base_url('dashboard/enquiries'); ?>">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.2">
                            <path
                                d="M16.4076 8.11328L12.3346 11.4252C11.5651 12.0357 10.4824 12.0357 9.71285 11.4252L5.60547 8.11328"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M15.4985 19.25C18.2864 19.2577 20.1654 16.9671 20.1654 14.1518V7.85584C20.1654 5.04059 18.2864 2.75 15.4985 2.75H6.49891C3.711 2.75 1.83203 5.04059 1.83203 7.85584V14.1518C1.83203 16.9671 3.711 19.2577 6.49891 19.25H15.4985Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </g>
                    </svg>
                    My Enquiries
                </a>
            </li>
            <li class="nav-menu-item">
                <a class="nav-menu-link" href="#" id="deleteAccountBtn" style="cursor: pointer; color: #ff6b6b;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.2">
                            <path
                                d="M7.33333 5.5V4.58333C7.33333 3.57081 8.15414 2.75 9.16667 2.75H12.8333C13.8459 2.75 14.6667 3.57081 14.6667 4.58333V5.5M17.4167 5.5V17.4167C17.4167 18.4292 16.5959 19.25 15.5833 19.25H6.41667C5.40414 19.25 4.58333 18.4292 4.58333 17.4167V5.5M9.16667 9.16667V15.5833M12.8333 9.16667V15.5833"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </g>
                    </svg>
                    Delete Account
                </a>
            </li>
            <li class="nav-menu-item">
                <a class="nav-menu-link" href="#" id="dashboardLogoutBtn" style="cursor: pointer;">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.2">
                            <path
                                d="M13.7627 6.77418V5.91893C13.7627 4.05352 12.2502 2.54102 10.3848 2.54102H5.91606C4.05156 2.54102 2.53906 4.05352 2.53906 5.91893V16.1214C2.53906 17.9868 4.05156 19.4993 5.91606 19.4993H10.394C12.2539 19.4993 13.7627 17.9914 13.7627 16.1315V15.2671"
                                stroke="#F1FAEE" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M19.9907 11.0208H8.95312" stroke="#F1FAEE" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17.3047 8.34766L19.9887 11.0197L17.3047 13.6927" stroke="#F1FAEE"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                    </svg>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- end sidebar dashboard -->