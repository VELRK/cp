<div class="main-content">
    <div class="main-content-inner">
        <div class="button-show-hide show-mb">
            <span class="body-1">Show Dashboard</span>
        </div>
        
        <div class="wrapper-content row">
            <div class="col-xl-12">
                <div class="widget-box-2 wd-listing">
                    <h5 class="title">My Wishlist</h5>
                    <div class="d-flex gap-4">
                        <span class="text-primary fw-7"><?php echo count($wishlist); ?></span>
                        <span class="fw-6">Properties found</span>
                    </div>
                    
                    <?php if (!empty($wishlist)): ?>
                        <div class="wrap-table">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($wishlist as $item): ?>
                                            <tr class="file-delete">
                                                <td>
                                                    <div class="listing-box">
                                                        <div class="images">
                                                            <img src="<?php echo $item['propertyImage']; ?>" alt="<?php echo $item['propertyName']; ?>">
                                                        </div>
                                                        <div class="content">
                                                            <div class="title">
                                                                <?php
                                                                $wish_pid = $item['propertyId'] ?? '';
                                                                $wish_url = !empty($wish_pid)
                                                                    ? base_url('property-detail/' . rawurlencode($wish_pid))
                                                                    : base_url('our-projects');
                                                                ?>
                                                                <a href="<?php echo htmlspecialchars($wish_url, ENT_QUOTES, 'UTF-8'); ?>" class="link">
                                                                    <?php echo htmlspecialchars($item['propertyName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            </div>
                                                            <div class="text-date">Added: <?php echo date('M d, Y', strtotime($item['addedAt'])); ?></div>
                                                            <div class="text-btn text-primary"><?php
                                                                $pp = $item['propertyPrice'] ?? '';
                                                                echo (is_numeric($pp) && (float) $pp > 0) ? dvm_format_price_inr((float) $pp, true) : htmlspecialchars((string) $pp);
                                                            ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="icon">❤️</div>
                            <h4>No Properties in Wishlist</h4>
                            <p>You haven't added any properties to your wishlist yet.</p>
                            <a href="<?php echo base_url(); ?>" class="btn btn-primary">Browse Properties</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-dashboard">
        <p>Copyright © 2024 Home Lengo</p>
    </div>
</div>