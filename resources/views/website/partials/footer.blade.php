    <!-- Start Footer -->
    <footer class="footer-box">
        <div class="container">

           <div class="row">

              <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                 <div class="footer_blog">
                    <div class="full margin-bottom_30">
                        <img style="height:80px;width:100px" src="{{ asset(\App\WebsiteSetting::get('footer_logo', 'images/logo.png')) }}" alt="image">
                    </div>
                    <div class="full white_fonts">
                        <p>{{ \App\WebsiteSetting::get('footer_vision_text', 'Our Vision is provide a well-groomed, enriched (in ideas) and productive learner given a firm foundation for tertiary and life challenges.') }}</p>
                    </div>
                 </div>
              </div>

              <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                   <div class="footer_blog footer_menu white_fonts">
                            <h3>Quick links</h3>
                            <ul>
                              <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_1', 'Join Us') }}</a></li>
                              <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_2', 'Maintenance') }}</a></li>
                              <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_3', 'Language Packs') }}</a></li>
                              <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_4', 'LearnPress') }}</a></li>
                              <li><a href="#">> {{ \App\WebsiteSetting::get('footer_quick_link_5', 'Release Status') }}</a></li>
                            </ul>
                         </div>
             </div>

             <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
             <div class="footer_blog full white_fonts">
                             <h3>Newsletter</h3>

                             <div class="newsletter_form">
                                <form action="{{ route('website.News') }}">
                                   <input type="email" placeholder="Your Email" name="email" required />
                                   <button type="submit">Submit</button>
                                </form>
                             </div>
                         </div>
                </div>

          <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
             <div class="footer_blog full white_fonts">
                             <h3>Contact us</h3>
                             <ul class="full">
               <li><img src="{{ asset('images/i5.png') }}"><span>{{ \App\WebsiteSetting::get('address', '6884 Mt Madecheche Road, Zimre Park') }}</span></li>
                               <li><img src="{{ asset('images/i6.png') }}"><span>{{ \App\WebsiteSetting::get('contact_email', 'info@roshs.co.zw') }}</span></li>
                               <li><img src="{{ asset('images/i7.png') }}"><span>{{ \App\WebsiteSetting::get('contact_phone', '+263 772 490 478') }}</span></li>
                             </ul>
                         </div>
                </div>

           </div>

        </div>
    </footer>
    <!-- End Footer -->

    <div class="footer_bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p style="color: white">
                    Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This website is made with <i class="icon-heart" aria-hidden="true"></i> by <a style="color: white" href="https://lotusdreammaker.co.zw" target="_blank">Lotusdreammaker</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
