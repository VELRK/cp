<?php
$phone_tel = isset($contact_phone_tel) ? $contact_phone_tel : '+918988982030';
$phone_display = isset($contact_phone_display) ? $contact_phone_display : '+91 89889 82030';
$email_addr = isset($contact_email) ? $contact_email : 'reachmr.karthick@gmail.com';
$contact_map_query = 'Site No 268, Royal Castle, Karuvalur Road, Kovilpalayam, Coimbatore, Tamil Nadu 641107, India';
$contact_maps_open_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($contact_map_query);
$contact_map_embed_url = 'https://www.google.com/maps?q=' . rawurlencode($contact_map_query) . '&output=embed&z=16&hl=en';
?>
                     <section class="flat-title-page flat-title-page--contact">
                <div class="container">
                    <div class="breadcrumb-content">
                        <ul class="breadcrumb">
                            <li><a href="<?php echo base_url(); ?>" class="text-white">Home</a></li>                            
                            <li class="text-white">/ Contact Us</li>
                        </ul>
                        <h1 class="text-center text-white title">Contact Us</h1>
                        <div class="contact-page-hero-meta text-center mt-3 d-flex flex-wrap justify-content-center align-items-center">
                            <a href="tel:<?php echo preg_replace('/\s+/', '', $phone_tel); ?>" class="link-primary d-inline-flex align-items-center justify-content-center gap-2 mx-2 mx-md-3 mb-2 mb-md-0 contact-hero-link">
                                <span class="contact-hero-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                </span>
                                <span><strong>Mobile:</strong> <?php echo htmlspecialchars($phone_display); ?></span>
                            </a>
                            <span class="text-white d-none d-md-inline opacity-75 mx-1">|</span>
                            <a href="mailto:<?php echo htmlspecialchars($email_addr); ?>" class="link-primary d-inline-flex align-items-center justify-content-center gap-2 mx-2 mx-md-3 contact-hero-link">
                                <span class="contact-hero-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                </span>
                                <span><strong>Mail:</strong> <?php echo htmlspecialchars($email_addr); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>  
		   <section class="flat-section flat-contact">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="contact-content">
                                <h4>Drop Us A Line</h4>
                                <p class="body-2 text-variant-1">Feel free to connect with us through our online
                                    channels for updates, news, and more.</p>
                                <form id="contactform" method="post" action="<?php echo base_url('contact/submit'); ?>" class="form-contact">
                                    <div class="box grid-2">
                                        <fieldset>
                                            <label for="name">Full Name:</label>
                                            <input type="text" class="form-control" placeholder="Your name" name="name"
                                                id="name" required>
                                        </fieldset>
                                        <fieldset>
                                            <label for="email">Email Address:</label>
                                            <input type="text" class="form-control" placeholder="Email" name="email"
                                                id="email" required>
                                        </fieldset>
                                    </div>
                                    <div class="box grid-2">
                                        <fieldset>
                                            <label for="phone">Mobile number</label>
                                            <input type="tel" class="form-control style-1" placeholder="+91 98765 43210"
                                                name="phone" id="phone" inputmode="tel" autocomplete="tel" maxlength="16" required>
                                            <small class="text-variant-1 d-block mt-1">10-digit Indian mobile: use +91 and space as shown (e.g. <?php echo htmlspecialchars($phone_display); ?>).</small>
                                        </fieldset>
                                        <fieldset>
                                            <label for="subject">Subject:</label>
                                            <input type="text" class="form-control style-1" placeholder="Enter Keyword"
                                                name="subject" id="subject">
                                        </fieldset>
                                    </div>
                                    <fieldset>
                                        <label for="message">Your Message:</label>
                                        <textarea name="message" class="form-control" cols="30" rows="10"
                                            placeholder="Message" id="message" required></textarea>
                                    </fieldset>
                                    <div class="send-wrap">
                                        <button class="tf-btn primary size-1" type="submit" id="contactSubmitBtn">Send Message</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="contact-info">
                                <h4>Contact Us</h4>
                                <ul>
                                    <li class="box">
                                        <h6 class="title">Address:</h6>
                                        <p class="text-variant-1">Site No: 268, Royal Castle
                                            <br> Karuvalur Road,
                                            <br> Kovilpalayam – 641107
                                            <br> Coimbatore, Tamil Nadu
                                        </p>
                                    </li>
                                    <li class="box">
                                        <h6 class="title">Mobile No:</h6>
                                        <p class="text-variant-1 mb-3">
                                            <a href="tel:<?php echo preg_replace('/\s+/', '', $phone_tel); ?>" class="link-primary text-decoration-underline"><?php echo htmlspecialchars($phone_display); ?></a>
                                        </p>
                                        <h6 class="title">Mail ID:</h6>
                                        <p class="text-variant-1 mb-0">
                                            <a href="mailto:<?php echo htmlspecialchars($email_addr); ?>" class="link-primary text-decoration-underline"><?php echo htmlspecialchars($email_addr); ?></a>
                                        </p>
                                    </li>
                                    <li class="box">
                                        <div class="title">Opentime:</div>
                                        <p class="text-variant-1">Monay - Friday: 08:00 - 20:00 <br> Saturday - Sunday:
                                            10:00 - 18:00</p>

                                    </li>
                                    <li class="box">
                                        <div class="title">Follow Us:</div>
                                        <ul class="box-social">
                                            <li><a href="https://www.facebook.com/Dreamvillamakers" class="item" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                                    <svg width="10" height="18" viewBox="0 0 10 18" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M9.00879 10.125L9.50871 6.86742H6.38297V4.75348C6.38297 3.86227 6.81961 2.99355 8.21953 2.99355H9.64055V0.220078C9.64055 0.220078 8.35102 0 7.11809 0C4.54395 0 2.86137 1.56023 2.86137 4.38469V6.86742H0V10.125H2.86137V18H6.38297V10.125H9.00879Z"
                                                            fill="#161E2D" />
                                                    </svg>
                                                </a></li>
                                            <li><a href="https://www.instagram.com/dreamvillamakers" class="item" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M9.00245 4.38427C6.4484 4.38427 4.38828 6.44438 4.38828 8.99844C4.38828 11.5525 6.4484 13.6126 9.00245 13.6126C11.5565 13.6126 13.6166 11.5525 13.6166 8.99844C13.6166 6.44438 11.5565 4.38427 9.00245 4.38427ZM9.00245 11.9983C7.35195 11.9983 6.00264 10.653 6.00264 8.99844C6.00264 7.34392 7.34794 5.99862 9.00245 5.99862C10.657 5.99862 12.0023 7.34392 12.0023 8.99844C12.0023 10.653 10.653 11.9983 9.00245 11.9983ZM14.8816 4.19552C14.8816 4.79388 14.3997 5.27176 13.8054 5.27176C13.207 5.27176 12.7291 4.78986 12.7291 4.19552C12.7291 3.60118 13.211 3.11928 13.8054 3.11928C14.3997 3.11928 14.8816 3.60118 14.8816 4.19552ZM17.9376 5.28782C17.8694 3.84615 17.5401 2.56912 16.4839 1.51697C15.4318 0.46483 14.1547 0.135534 12.7131 0.0632491C11.2272 -0.021083 6.77368 -0.021083 5.28782 0.0632491C3.85016 0.131518 2.57313 0.460815 1.51697 1.51296C0.460815 2.5651 0.135534 3.84213 0.0632491 5.28381C-0.021083 6.76966 -0.021083 11.2232 0.0632491 12.7091C0.131518 14.1507 0.460815 15.4278 1.51697 16.4799C2.57313 17.532 3.84615 17.8613 5.28782 17.9336C6.77368 18.018 11.2272 18.018 12.7131 17.9336C14.1547 17.8654 15.4318 17.5361 16.4839 16.4799C17.5361 15.4278 17.8654 14.1507 17.9376 12.7091C18.022 11.2232 18.022 6.77368 17.9376 5.28782ZM16.0181 14.3033C15.7048 15.0904 15.0985 15.6968 14.3073 16.0141C13.1227 16.4839 10.3116 16.3755 9.00245 16.3755C7.6933 16.3755 4.87821 16.4799 3.69756 16.0141C2.91046 15.7008 2.30407 15.0944 1.98682 14.3033C1.51697 13.1187 1.6254 10.3076 1.6254 8.99844C1.6254 7.68928 1.52099 4.8742 1.98682 3.69355C2.30006 2.90645 2.90645 2.30006 3.69756 1.98281C4.88223 1.51296 7.6933 1.62139 9.00245 1.62139C10.3116 1.62139 13.1267 1.51697 14.3073 1.98281C15.0944 2.29604 15.7008 2.90243 16.0181 3.69355C16.4879 4.87821 16.3795 7.68928 16.3795 8.99844C16.3795 10.3076 16.4879 13.1227 16.0181 14.3033Z"
                                                            fill="#161E2D" />
                                                    </svg>
                                                </a></li>
                                            <li><a href="https://www.youtube.com/@DreamVillaMakers" class="item" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                                                    <svg width="20" height="14" viewBox="0 0 20 14" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M19.2775 2.16608C19.051 1.31346 18.3839 0.641967 17.5368 0.414086C16.0013 0 9.84445 0 9.84445 0C9.84445 0 3.68759 0 2.15212 0.414086C1.30502 0.642003 0.637857 1.31346 0.411419 2.16608C0 3.71149 0 6.93586 0 6.93586C0 6.93586 0 10.1602 0.411419 11.7056C0.637857 12.5583 1.30502 13.2018 2.15212 13.4296C3.68759 13.8437 9.84445 13.8437 9.84445 13.8437C9.84445 13.8437 16.0013 13.8437 17.5368 13.4296C18.3839 13.2018 19.051 12.5583 19.2775 11.7056C19.6889 10.1602 19.6889 6.93586 19.6889 6.93586C19.6889 6.93586 19.6889 3.71149 19.2775 2.16608ZM7.8308 9.86334V4.00837L12.9767 6.93593L7.8308 9.86334Z"
                                                            fill="#161E2D" />
                                                    </svg>
                                                </a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5 pt-lg-3">
                        <div class="col-12">
                            <div class="contact-map-block">
                                <h4 class="mb-3">Find us on the map</h4>
                                <p class="body-2 text-variant-1 mb-3">
                                    Site No: 268, Royal Castle<br>
                                    Karuvalur Road,<br>
                                    Kovilpalayam – 641107<br>
                                    Coimbatore, Tamil Nadu
                                </p>
                                <div class="contact-map-wrap ratio ratio-16x9">
                                    <iframe
                                        title="Dream Villa Makers — location on Google Maps"
                                        src="<?php echo htmlspecialchars($contact_map_embed_url); ?>"
                                        class="contact-map-iframe"
                                        loading="lazy"
                                        allowfullscreen
                                        referrerpolicy="no-referrer-when-downgrade"
                                        aria-label="Google Map showing our office location"></iframe>
                                </div>
                                <p class="mt-3 mb-0">
                                    <a href="<?php echo htmlspecialchars($contact_maps_open_url); ?>" class="link-primary text-decoration-underline" target="_blank" rel="noopener noreferrer">Open this location in Google Maps</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


       
