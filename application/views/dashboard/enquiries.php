<style>
    .wrap-table .listing-box .images {
        width: 100px !important;
    }
    .user-details-cell {
        min-width: 200px;
    }
    .user-details-cell strong {
        color: #2c3e50;
        font-size: 14px;
        display: block;
        margin-bottom: 4px;
    }
    .user-details-cell .user-info {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 4px;
    }
    .user-details-cell .user-info i {
        width: 16px;
        margin-right: 4px;
    }
</style>


<div class="main-content">
    <div class="main-content-inner">
        <div class="button-show-hide show-mb">
            <span class="body-1">Show Dashboard</span>
        </div>
        
        <div class="wrapper-content row">
            <div class="col-xl-12">
                <div class="widget-box-2 wd-listing">
                    <h5 class="title">My Enquiries</h5>
                    <div class="d-flex gap-4">
                        <span class="text-primary fw-7"><?php echo count($enquiries); ?></span>
                        <span class="fw-6">Enquiries found</span>
                    </div>
                    
                    <?php if (!empty($enquiries)): ?>
                        <div class="wrap-table">
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>User Details</th>
                                            <th>Enquiry Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($enquiries as $item): ?>
                                            <tr class="file-delete">
                                                <td>
                                                    <div class="listing-box">
                                                        <div class="images">
                                                            <img src="<?php echo isset($item['coverImageUrl']) ? $item['coverImageUrl'] : base_url('assets/images/home/house-18.jpg'); ?>" 
                                                                 alt="<?php echo htmlspecialchars($item['propertyName'] ?? 'Property'); ?>"
                                                                 style="width: 100px; height: 80px; object-fit: cover; border-radius: 8px;">
                                                        </div>
                                                        <div class="content">
                                                            <div class="title">
                                                                <?php
                                                                $enquiry_prop_id = $item['propertyId'] ?? $item['property_id'] ?? '';
                                                                $enquiry_prop_url = !empty($enquiry_prop_id)
                                                                    ? base_url('property-detail/' . rawurlencode($enquiry_prop_id))
                                                                    : base_url('our-projects');
                                                                ?>
                                                                <a href="<?php echo htmlspecialchars($enquiry_prop_url, ENT_QUOTES, 'UTF-8'); ?>" class="link">
                                                                    <?php echo htmlspecialchars($item['propertyName'] ?? 'Property'); ?>
                                                                </a>
                                                            </div>
                                                            <div class="text-date">Enquiry: <?php 
                                                                $date = isset($item['createdAt']) ? $item['createdAt'] : date('Y-m-d');
                                                                echo date('M d, Y', strtotime($date)); 
                                                            ?></div>
                                                            <div class="text-btn text-primary"><?php
                                                                $pp = $item['propertyPrice'] ?? '';
                                                                if ($pp === '' || $pp === null) {
                                                                    echo 'Price on Request';
                                                                } elseif (is_numeric($pp) && (float) $pp > 0) {
                                                                    echo dvm_format_price_inr((float) $pp, true);
                                                                } else {
                                                                    echo htmlspecialchars((string) $pp);
                                                                }
                                                            ?></div>
                                                            <?php if (!empty($item['message'])): ?>
                                                                <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                                                                    <?php echo htmlspecialchars(substr($item['message'], 0, 50)); ?><?php echo strlen($item['message']) > 50 ? '...' : ''; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <div style="margin-bottom: 8px;">
                                                            <strong style="color: #2c3e50; font-size: 14px;"><?php echo htmlspecialchars($item['userName'] ?? 'N/A'); ?></strong>
                                                        </div>
                                                        <div style="font-size: 13px; color: #6c757d; margin-bottom: 4px;">
                                                            <i class="fas fa-envelope" style="width: 16px;"></i> <?php echo htmlspecialchars($item['userEmail'] ?? 'N/A'); ?>
                                                        </div>
                                                        <?php if (!empty($item['userPhone'])): ?>
                                                        <div style="font-size: 13px; color: #6c757d;">
                                                            <i class="fas fa-phone" style="width: 16px;"></i> <?php echo htmlspecialchars($item['userPhone']); ?>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $item['enquiryType'])); ?></span>
                                                </td>
                                                <td>
                                                    <div class="status-wrap">
                                                        <a href="#" class="btn-status <?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted"><?php echo date('M d, Y', strtotime($item['createdAt'])); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="icon">📧</div>
                            <h4>No Enquiries Found</h4>
                            <p>You haven't made any property enquiries yet.</p>
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