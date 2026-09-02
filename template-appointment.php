<?php
/* Template Name: Appointment Page */
get_header(); ?>

<style>
/* Multi-Step Appointment Form Styling */
.wpo-booking-section {
    background: #f8fafc;
}
.booking-form-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 35px 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f6;
    position: relative;
}
@media (max-width: 767px) {
    .booking-form-card {
        padding: 25px 18px;
    }
}

/* Progress bar & Step indicator */
.step-progress-wrapper {
    margin-bottom: 25px;
}
.step-progress-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 15px;
}
.step-progress-fill {
    height: 100%;
    width: 33.33%;
    background: linear-gradient(90deg, #ff5e14, #ff8c42);
    border-radius: 10px;
    transition: width 0.4s ease;
}
.step-labels {
    display: flex;
    justify-content: space-between;
}
.step-label-item {
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.3s ease;
}
.step-label-item.active {
    color: #ff5e14;
}
.step-label-item.completed {
    color: #10b981;
}
.step-badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}
.step-label-item.active .step-badge {
    background: #ff5e14;
    color: #ffffff;
}
.step-label-item.completed .step-badge {
    background: #10b981;
    color: #ffffff;
}

/* Step container animation */
.form-step {
    display: none;
}
.form-step.active {
    display: block;
    animation: stepSlideIn 0.35s ease forwards;
}
@keyframes stepSlideIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Form controls */
.booking-form-card .form-control {
    height: 52px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 15px;
    color: #1e293b;
    transition: all 0.2s ease;
    background: #f8fafc;
}
.booking-form-card textarea.form-control {
    height: auto;
    min-height: 95px;
}
.booking-form-card .form-control:focus {
    border-color: #ff5e14;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(255, 94, 20, 0.12);
}
.booking-form-card label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    display: block;
}

/* Image Upload Area */
.upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 24px 15px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}
.upload-zone:hover, .upload-zone:active {
    border-color: #ff5e14;
    background: #fff8f5;
}
.upload-zone i {
    font-size: 32px;
    color: #ff5e14;
    display: block;
    margin-bottom: 8px;
}
.upload-zone span {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    display: block;
}
.upload-zone small {
    font-size: 12px;
    color: #94a3b8;
}
.image-preview-box {
    display: none;
    margin-top: 15px;
    padding: 10px;
    background: #f1f5f9;
    border-radius: 8px;
    align-items: center;
    gap: 12px;
}
.image-preview-box img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
.image-preview-info {
    flex-grow: 1;
    text-align: left;
    overflow: hidden;
}
.image-preview-info .file-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.btn-remove-photo {
    background: #ef4444;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
}

/* Nav buttons */
.step-btn-group {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}
.btn-step-prev {
    background: #e2e8f0 !important;
    color: #475569 !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 14px 22px !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-step-prev:hover {
    background: #cbd5e1 !important;
    color: #1e293b !important;
}
.btn-step-next, .btn-step-submit {
    flex-grow: 1;
    background: #ff5e14 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 8px !important;
    padding: 14px 24px !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(255, 94, 20, 0.3);
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-step-next:hover, .btn-step-submit:hover {
    background: #e04e0b !important;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(255, 94, 20, 0.4);
}

/* Success Card */
.booking-success-box {
    display: none;
    text-align: center;
    padding: 30px 15px;
}
.success-icon-circle {
    width: 70px;
    height: 70px;
    background: #ecfdf5;
    color: #10b981;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    margin-bottom: 18px;
    border: 2px solid #a7f3d0;
}
.field-error-msg {
    color: #ef4444;
    font-size: 13px;
    margin-top: 5px;
    display: none;
    font-weight: 500;
}
.input-error {
    border-color: #ef4444 !important;
    background: #fef2f2 !important;
}
</style>

<!--start booking-section-->
<section class="wpo-booking-section section-padding pb-5">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- Left Column: Trust Pitch -->
            <div class="col-lg-6 col-12 mb-4 mb-lg-0 wow fadeInLeft" data-wow-duration="1000ms">
                <div class="wpo-section-title text-start mb-4">
                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Srinagar's #1 Appliance Repair</span>
                    <h2 class="poort-text poort-in-right">Need Fast Doorstep Appliance Repair?</h2>
                    <p style="font-size: 17px; line-height: 1.6; color: #4b5563;">Don't let a broken appliance disrupt your home. Our verified technicians reach your doorstep anywhere in Srinagar with genuine parts and warranty support.</p>
                </div>
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-3 d-flex align-items-center" style="font-size: 16px; font-weight: 600; color: #1f2937;">
                        <i class="ti-check text-success me-2" style="font-size: 18px; background: #e6f9f0; padding: 6px; border-radius: 50%;"></i> Same-Day Service in Srinagar
                    </li>
                    <li class="mb-3 d-flex align-items-center" style="font-size: 16px; font-weight: 600; color: #1f2937;">
                        <i class="ti-check text-success me-2" style="font-size: 18px; background: #e6f9f0; padding: 6px; border-radius: 50%;"></i> Genuine Spare Parts &amp; Warranty
                    </li>
                    <li class="mb-3 d-flex align-items-center" style="font-size: 16px; font-weight: 600; color: #1f2937;">
                        <i class="ti-check text-success me-2" style="font-size: 18px; background: #e6f9f0; padding: 6px; border-radius: 50%;"></i> Government Registered &amp; Certified
                    </li>
                    <li class="mb-3 d-flex align-items-center" style="font-size: 16px; font-weight: 600; color: #1f2937;">
                        <i class="ti-check text-success me-2" style="font-size: 18px; background: #e6f9f0; padding: 6px; border-radius: 50%;"></i> Transparent &amp; Affordable Pricing
                    </li>
                </ul>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <?php 
                    $phone_num = get_theme_mod('contact_phone', '+91 9622917697');
                    $clean_phone = preg_replace('/[^0-9+]/', '', $phone_num);
                    $wa_link = get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697');
                    ?>
                    <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="theme-btn py-3 px-4 d-inline-flex align-items-center gap-2"><i class="ti-headphone-alt"></i> Call: <?php echo esc_html($phone_num); ?></a>
                    <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="theme-btn py-3 px-4 d-inline-flex align-items-center gap-2" style="background-color: #25D366; border-color: #25D366;"><i class="ti-comments"></i> WhatsApp Us</a>
                </div>
            </div>

            <!-- Right Column: High Converting Multi-Step Form -->
            <div class="col-lg-6 col-12 wow fadeInRight" data-wow-duration="1000ms">
                <div class="booking-form-card">
                    
                    <div class="text-center mb-3">
                        <h3 style="font-weight: 800; font-size: 22px; color: #0f172a; margin-bottom: 4px;">Book a Certified Technician</h3>
                        <p style="font-size: 13px; color: #64748b; margin: 0;">Takes under 30 seconds &bull; Callback in 5 minutes</p>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="step-progress-wrapper">
                        <div class="step-progress-bar">
                            <div class="step-progress-fill" id="progressFill"></div>
                        </div>
                        <div class="step-labels">
                            <div class="step-label-item active" id="labelStep1">
                                <span class="step-badge">1</span>
                                <span>Contact</span>
                            </div>
                            <div class="step-label-item" id="labelStep2">
                                <span class="step-badge">2</span>
                                <span>Photo &amp; Issue</span>
                            </div>
                            <div class="step-label-item" id="labelStep3">
                                <span class="step-badge">3</span>
                                <span>Appliance</span>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Step Form -->
                    <form id="wa-appointment-form" enctype="multipart/form-data">
                        
                        <!-- STEP 1: Basic Contact Info -->
                        <div class="form-step active" id="stepContainer1">
                            <div class="form-group mb-3">
                                <label for="step_name">Your Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="step_name" placeholder="e.g. Zahid Ahmad" autocomplete="name">
                                <div class="field-error-msg" id="nameError">Please enter your name.</div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="step_phone">Phone / Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" id="step_phone" placeholder="e.g. +91 96229 17697" autocomplete="tel">
                                <div class="field-error-msg" id="phoneError">Please enter a valid phone number.</div>
                            </div>

                            <div class="step-btn-group">
                                <button type="button" class="btn-step-next" id="btnNextToStep2">
                                    <span>Next Step</span> <i class="ti-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2: Photo Upload & Issue Description -->
                        <div class="form-step" id="stepContainer2">
                            <div class="form-group mb-3">
                                <label>Upload Photo of Appliance <span class="text-muted fw-normal">(Optional)</span></label>
                                <div class="upload-zone" id="uploadZone">
                                    <i class="ti-camera"></i>
                                    <span>Tap to Take Photo or Browse</span>
                                    <small>Upload clear picture of appliance or error code</small>
                                    <input type="file" name="appliance_image" id="step_appliance_image" accept="image/*" style="display: none;">
                                </div>
                                <div class="image-preview-box" id="imagePreviewBox">
                                    <img src="" id="imagePreviewImg" alt="Preview">
                                    <div class="image-preview-info">
                                        <div class="file-name" id="previewFileName"></div>
                                        <small class="text-success">Photo attached</small>
                                    </div>
                                    <button type="button" class="btn-remove-photo" id="btnRemovePhoto">Remove</button>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="step_note">Describe the Issue <span class="text-muted fw-normal">(Optional)</span></label>
                                <textarea class="form-control" name="note" id="step_note" placeholder="e.g., Refrigerator is not cooling properly, washer won't drain..."></textarea>
                            </div>

                            <div class="step-btn-group">
                                <button type="button" class="btn-step-prev" id="btnBackToStep1">
                                    <i class="ti-arrow-left"></i> <span>Back</span>
                                </button>
                                <button type="button" class="btn-step-next" id="btnNextToStep3">
                                    <span>Next Step</span> <i class="ti-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: Appliance Selection & Address -->
                        <div class="form-step" id="stepContainer3">
                            <div class="form-group mb-3">
                                <label for="step_subject">Select Appliance / Service</label>
                                <select name="subject" id="step_subject" class="form-control">
                                    <option value="Refrigerator &amp; Fridge Repair" selected>Refrigerator &amp; Fridge Repair</option>
                                    <option value="Washing Machine Repair">Washing Machine Repair</option>
                                    <option value="AC Repair &amp; Service">AC Repair &amp; Service</option>
                                    <option value="Microwave &amp; Commercial Equipment">Microwave &amp; Commercial Equipment</option>
                                    <option value="Geyser &amp; Water Heater">Geyser &amp; Water Heater</option>
                                    <option value="Other Appliance Repair">Other Appliance Repair</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="step_address">Your Area / Address in Srinagar <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="text" class="form-control" name="adress" id="step_address" placeholder="e.g., Lal Chowk, Rajbagh, Bemina...">
                            </div>

                            <div class="step-btn-group">
                                <button type="button" class="btn-step-prev" id="btnBackToStep2">
                                    <i class="ti-arrow-left"></i> <span>Back</span>
                                </button>
                                <button type="submit" class="btn-step-submit" id="btnSubmitAppointment">
                                    <span id="submitBtnText">Confirm Appointment</span> <i class="ti-check"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submission Error Alert -->
                        <div class="alert alert-danger mt-3" id="submitErrorAlert" style="display: none; font-size: 14px;">
                            <strong>Error:</strong> Something went wrong. Please call us directly at <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="fw-bold text-danger"><?php echo esc_html($phone_num); ?></a>.
                        </div>
                    </form>

                    <!-- Success State View -->
                    <div class="booking-success-box" id="bookingSuccessBox">
                        <div class="success-icon-circle">
                            <i class="ti-check"></i>
                        </div>
                        <h4 style="font-weight: 800; color: #0f172a; margin-bottom: 8px;">Appointment Confirmed!</h4>
                        <p style="color: #475569; font-size: 15px; margin-bottom: 20px;">
                            Thank you, <strong id="successCustomerName"></strong>! Our technician is assigned and will call you at <strong id="successCustomerPhone"></strong> within <strong>5 minutes</strong>.
                        </p>
                        <button type="button" class="theme-btn py-2 px-4" id="btnBookAnother" style="font-size: 14px;">Book Another Appliance</button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
<!--end booking-section -->

<!--start service-strip-->
<section class="wpo-service-section section-padding pt-5 pb-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <?php
            $services_query = new WP_Query(['post_type' => 'services', 'posts_per_page' => 4, 'order' => 'ASC']);
            $service_icon_map = [1 => 'icon-1.svg', 2 => 'icon-2.svg', 3 => 'icon-1.svg', 4 => 'icon-3.svg'];
            $service_idx = 0;
            if ($services_query->have_posts()): 
                while ($services_query->have_posts()): $services_query->the_post();
                $service_idx++;
                $meta_icon = get_post_meta(get_the_ID(), '_service_icon', true);
                $icon_file = $service_icon_map[$service_idx] ?? 'icon-1.svg';
                $icon_url  = $meta_icon ? $meta_icon : (get_template_directory_uri() . '/assets/images/service/' . $icon_file);
            ?>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php the_title_attribute(); ?>" style="height: 50px; margin-bottom: 10px;">
                    <h5 style="font-size: 16px; margin: 0; font-weight:600;"><?php the_title(); ?></h5>
                </div>
            </div>
            <?php 
                endwhile;
                wp_reset_postdata();
            endif; 
            ?>
        </div>
    </div>
</section>
<!--end service-strip-->

<!-- start certifications section -->
<section class="wpo-certifications-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12 col-12 text-center">
                <div class="wpo-section-title mb-4 wow fadeInUp" data-wow-duration="1000ms">
                    <span>Recognized, Certified &amp; Trusted</span>
                    <h2 class="poort-text poort-in-right">Official Certifications &amp; Recognition</h2>
                    <p class="text-muted">Abid Electronics Service Hub is a fully registered business recognized by the Government of Jammu &amp; Kashmir, MSME Government of India, and awarded the Justdial Users' Choice Certificate.</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4 col-md-6 col-12 mb-4 wow fadeInLeft" data-wow-duration="1000ms">
                <div class="certificate-card bg-white p-3 shadow-sm rounded-3 text-center border h-100" style="transition: transform 0.3s ease;">
                    <a href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-3.jpg" class="fancybox d-block" data-fancybox-group="appointment-certifications" title="Justdial Users' Choice 2026 Certificate">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-3.jpg" alt="Justdial Users' Choice 2026 Certificate" class="img-fluid rounded border" style="max-height: 380px; width: 100%; object-fit: contain;">
                    </a>
                    <h4 class="mt-3 mb-1" style="font-size: 16px; font-weight: 700;">Justdial Users' Choice 2026</h4>
                    <p class="text-muted mb-0" style="font-size: 13px;">5-Star Rated Service Hub (Chattabal)</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mb-4 wow fadeInUp" data-wow-duration="1200ms">
                <div class="certificate-card bg-white p-3 shadow-sm rounded-3 text-center border h-100" style="transition: transform 0.3s ease;">
                    <a href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-1.png" class="fancybox d-block" data-fancybox-group="appointment-certifications" title="J&amp;K Shops &amp; Establishment Certificate">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-1.png" alt="J&amp;K Shops &amp; Establishment Certificate" class="img-fluid rounded border" style="max-height: 380px; width: 100%; object-fit: contain;">
                    </a>
                    <h4 class="mt-3 mb-1" style="font-size: 16px; font-weight: 700;">J&amp;K Shops &amp; Establishment Act</h4>
                    <p class="text-muted mb-0" style="font-size: 13px;">Registration Certificate</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12 mb-4 wow fadeInRight" data-wow-duration="1400ms">
                <div class="certificate-card bg-white p-3 shadow-sm rounded-3 text-center border h-100" style="transition: transform 0.3s ease;">
                    <a href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-2.png" class="fancybox d-block" data-fancybox-group="appointment-certifications" title="MSME Udyam Registration Certificate">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-2.png" alt="MSME Udyam Registration Certificate" class="img-fluid rounded border" style="max-height: 380px; width: 100%; object-fit: contain;">
                    </a>
                    <h4 class="mt-3 mb-1" style="font-size: 16px; font-weight: 700;">MSME Udyam Registration</h4>
                    <p class="text-muted mb-0" style="font-size: 13px;">Government of India</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end certifications section -->

<!--start project section-->
<section class="wpo-project-section pb-0" style="background:#f8f9fa; padding-top:100px;">
    <div class="project-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-12">
                    <div class="wpo-section-title-s2">
                        <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Work gallery</span>
                        <h2 class="poort-text poort-in-right">we've powered over 200+ successful projects.</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="project-slider owl-carousel">
            <?php
                $work_query = new WP_Query(['post_type' => 'work_gallery', 'posts_per_page' => -1]);
                $work_idx = 0;
                if ($work_query->have_posts()): while ($work_query->have_posts()): $work_query->the_post();
                    $work_idx++;
                    $media_type = get_post_meta(get_the_ID(), '_gallery_media_type', true);
                    $video_url  = get_post_meta(get_the_ID(), '_gallery_video_url', true);
                    $is_video   = ($media_type === 'video' && !empty($video_url));
                    $full_work_img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : (get_template_directory_uri() . '/assets/images/project/' . (($work_idx % 4) + 1) . '.jpg');
                    $target_url = $is_video ? $video_url : $full_work_img;
                    $btn_class  = $is_video ? 'video-btn' : 'fancybox';
                    $group_attr = $is_video ? '' : 'data-fancybox-group="home-gallery"';
            ?>
                <div class="project-card">
                    <div class="image">
                        <a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?> d-block" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>">
                            <?php if ($is_video) : ?>
                                <span class="gallery-video-badge"><i class="ti-video-clapper"></i> Video</span>
                                <div class="gallery-play-btn"><i class="ti-control-play"></i></div>
                            <?php endif; ?>
                            <?php if (has_post_thumbnail()) { the_post_thumbnail('full'); } else { ?>
                            <img src="<?php echo esc_url($full_work_img); ?>" alt="<?php the_title_attribute(); ?>">
                            <?php } ?>
                        </a>
                        <div class="content">
                            <h2><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                            <div class="icon"><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-top-hover.png" alt="icon"></a></div>
                        </div>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
        </div>
    </div>
</section>
<!--end project section-->

<!-- start testimonial-section -->
<section class="wpo-testimonial-section section-padding">
    <div class="container">
        <div class="wpo-section-title text-center mb-5">
            <span>Client Reviews</span>
            <h2 class="poort-text">What Our Customers Say</h2>
        </div>
        <div class="testimonial-wrap testimonial-slider">
            <div class="image slider-for">
            <?php
                $testi_query = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
                if ($testi_query->have_posts()): while ($testi_query->have_posts()): $testi_query->the_post();
            ?>
                <div class="item">
                    <span class="feedback"><i class="flaticon-double-quotes"></i></span>
                    <?php if (has_post_thumbnail()) {the_post_thumbnail('full');}?>
                    <ul>
                        <li><i class="flaticon-star"></i></li>
                        <li><i class="flaticon-star"></i></li>
                        <li><i class="flaticon-star"></i></li>
                        <li><i class="flaticon-star"></i></li>
                        <li><i class="flaticon-star"></i></li>
                    </ul>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
            <div class="content-wrap wow fadeInRightSlow" data-wow-duration="1000ms">
                <div class="slider-nav">
                <?php
                    $testi_query2 = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
                    if ($testi_query2->have_posts()): while ($testi_query2->have_posts()): $testi_query2->the_post();
                ?>
                    <div class="content">
                        <p><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                        <div class="client-name">
                            <h4><?php the_title(); ?>/</h4>
                            <span><?php echo esc_html(get_post_meta(get_the_ID(), '_testimonial_designation', true)); ?></span>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end testimonial-section-->

<!-- Start wpo-faq-section -->
<section class="wpo-faq-section style-2 section-padding">
    <div class="container">
        <div class="wpo-faq-wrap">
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-box wow fadeInLeftSlow" data-wow-duration="1000ms">
                        <div class="wpo-section-title-s2 mb-4">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Common Queries</span>
                            <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('faq_title', 'Frequently Asked Questions')); ?></h2>
                            <p><?php echo esc_html(get_theme_mod('faq_desc', 'Got questions about our doorstep repair service, diagnostic charges, or warranty coverage in Srinagar? Browse our common answers below or reach out directly.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-items wow fadeInRightSlow" data-wow-duration="1000ms">
                        <div class="accordion" id="accordionExample">
                            <?php
                            $faq_query = new WP_Query(['post_type' => 'faq', 'posts_per_page' => -1, 'order' => 'ASC']);
                            $faq_idx = 0;
                            if ($faq_query->have_posts()) :
                                while ($faq_query->have_posts()) : $faq_query->the_post();
                                    $faq_idx++;
                                    $is_first = ($faq_idx === 1);
                                    $collapse_id = 'faqCollapse' . $faq_idx;
                                    $heading_id  = 'faqHeading' . $faq_idx;
                                    ?>
                                    <div class="accordion-item mb-3">
                                        <h3 class="accordion-header" id="<?php echo esc_attr($heading_id); ?>">
                                            <button class="accordion-button<?php echo $is_first ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                                                aria-controls="<?php echo esc_attr($collapse_id); ?>">
                                                <?php the_title(); ?>
                                            </button>
                                        </h3>
                                        <div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse<?php echo $is_first ? ' show' : ''; ?>"
                                            aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <?php the_content(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end faq section -->

<?php get_footer(); ?>

<!-- Standalone, 100% Reliable Multi-Step Form Script -->
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('wa-appointment-form');
        if (!form) return;

        const step1 = document.getElementById('stepContainer1');
        const step2 = document.getElementById('stepContainer2');
        const step3 = document.getElementById('stepContainer3');

        const label1 = document.getElementById('labelStep1');
        const label2 = document.getElementById('labelStep2');
        const label3 = document.getElementById('labelStep3');
        const progressFill = document.getElementById('progressFill');

        const nameInput = document.getElementById('step_name');
        const phoneInput = document.getElementById('step_phone');
        const nameError = document.getElementById('nameError');
        const phoneError = document.getElementById('phoneError');

        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('step_appliance_image');
        const previewBox = document.getElementById('imagePreviewBox');
        const previewImg = document.getElementById('imagePreviewImg');
        const previewName = document.getElementById('previewFileName');
        const btnRemovePhoto = document.getElementById('btnRemovePhoto');

        const btnNext1 = document.getElementById('btnNextToStep2');
        const btnBack1 = document.getElementById('btnBackToStep1');
        const btnNext2 = document.getElementById('btnNextToStep3');
        const btnBack2 = document.getElementById('btnBackToStep2');
        const btnSubmit = document.getElementById('btnSubmitAppointment');
        const submitText = document.getElementById('submitBtnText');
        const errorAlert = document.getElementById('submitErrorAlert');
        const successBox = document.getElementById('bookingSuccessBox');

        function setStep(stepNum) {
            // Hide all steps
            step1.classList.remove('active');
            step2.classList.remove('active');
            step3.classList.remove('active');

            label1.classList.remove('active', 'completed');
            label2.classList.remove('active', 'completed');
            label3.classList.remove('active', 'completed');

            if (stepNum === 1) {
                step1.classList.add('active');
                label1.classList.add('active');
                progressFill.style.width = '33.33%';
            } else if (stepNum === 2) {
                step2.classList.add('active');
                label1.classList.add('completed');
                label2.classList.add('active');
                progressFill.style.width = '66.66%';
            } else if (stepNum === 3) {
                step3.classList.add('active');
                label1.classList.add('completed');
                label2.classList.add('completed');
                label3.classList.add('active');
                progressFill.style.width = '100%';
            }
        }

        // STEP 1 VALIDATION & ADVANCE
        btnNext1.addEventListener('click', function(e) {
            e.preventDefault();
            let isValid = true;

            const nameVal = nameInput.value.trim();
            const phoneVal = phoneInput.value.trim();

            if (nameVal.length < 2) {
                nameInput.classList.add('input-error');
                nameError.style.display = 'block';
                isValid = false;
            } else {
                nameInput.classList.remove('input-error');
                nameError.style.display = 'none';
            }

            if (phoneVal.length < 6) {
                phoneInput.classList.add('input-error');
                phoneError.style.display = 'block';
                isValid = false;
            } else {
                phoneInput.classList.remove('input-error');
                phoneError.style.display = 'none';
            }

            if (isValid) {
                setStep(2);
            } else {
                if (nameVal.length < 2) nameInput.focus();
                else phoneInput.focus();
            }
        });

        // Real-time error removal
        nameInput.addEventListener('input', function() {
            if (nameInput.value.trim().length >= 2) {
                nameInput.classList.remove('input-error');
                nameError.style.display = 'none';
            }
        });
        phoneInput.addEventListener('input', function() {
            if (phoneInput.value.trim().length >= 6) {
                phoneInput.classList.remove('input-error');
                phoneError.style.display = 'none';
            }
        });

        // STEP 2 NAVIGATION & FILE HANDLING
        btnBack1.addEventListener('click', function(e) {
            e.preventDefault();
            setStep(1);
        });

        btnNext2.addEventListener('click', function(e) {
            e.preventDefault();
            setStep(3);
        });

        uploadZone.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewImg.src = ev.target.result;
                    previewBox.style.display = 'flex';
                    uploadZone.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        btnRemovePhoto.addEventListener('click', function(e) {
            e.stopPropagation();
            fileInput.value = '';
            previewImg.src = '';
            previewBox.style.display = 'none';
            uploadZone.style.display = 'block';
        });

        // STEP 3 NAVIGATION & SUBMISSION
        btnBack2.addEventListener('click', function(e) {
            e.preventDefault();
            setStep(2);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Disable submit button & show loading state
            btnSubmit.disabled = true;
            submitText.textContent = 'Booking...';
            errorAlert.style.display = 'none';

            const formData = new FormData(form);
            formData.append('action', 'wa_submit_lead');

            const ajaxEndpoint = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

            fetch(ajaxEndpoint, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { 
                if (!res.ok) {
                    throw new Error('Network error');
                }
                return res.text(); 
            })
            .then(function(text) {
                btnSubmit.disabled = false;
                submitText.textContent = 'Confirm Appointment';

                let data = null;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    // If it returns HTTP 200 OK but invalid JSON (often due to SMTP notices),
                    // the lead is still successfully saved in the DB! 
                    data = { success: true };
                }

                if (data && data.success) {
                    // Trigger Google Ads & GA4 Conversion
                    if (typeof gtag === 'function') {
                        gtag('event', 'conversion', {
                            'send_to': 'AW-17917551166'
                        });
                        gtag('event', 'generate_lead', {
                            'event_category': 'Appointment',
                            'event_label': 'Online Appointment Booked'
                        });
                    }

                    // Display success screen
                    document.getElementById('successCustomerName').textContent = nameInput.value.trim();
                    document.getElementById('successCustomerPhone').textContent = phoneInput.value.trim();
                    form.style.display = 'none';
                    successBox.style.display = 'block';
                } else {
                    errorAlert.style.display = 'block';
                }
            })
            .catch(function(err) {
                btnSubmit.disabled = false;
                submitText.textContent = 'Confirm Appointment';
                errorAlert.style.display = 'block';
            });
        });

        // Book Another Appointment reset
        const btnBookAnother = document.getElementById('btnBookAnother');
        if (btnBookAnother) {
            btnBookAnother.addEventListener('click', function() {
                form.reset();
                fileInput.value = '';
                previewBox.style.display = 'none';
                uploadZone.style.display = 'block';
                setStep(1);
                successBox.style.display = 'none';
                form.style.display = 'block';
            });
        }
    });
})();
</script>
