jQuery(document).ready(function ($) {
  $('body').append("<div class='muun-members-popup'></div>");
  $('body').append("<div class='muun-members-popup-overlay'><span class='muun-members-popup-overlay__close'>close</span></div>");

  $('.muunmembers__member').live('click', function() {
    $('.muun-members-popup-overlay').css( 'display', 'block' );
    $('.muun-members-popup').css( 'display', 'block' );
    $('.muun-members-popup').append("<div class='muun-members-popup__container'> <h1 class='member-popup__name'></h1> <h2 class='member-popup__headline'></h2> <p class='member-popup__bio'></p> <div class='member-popup__expertises'></div> <div class='member-popup__urls'></div> </div> ");
    $('.member-popup__name').text($(this).attr('data-muun-name').replace(/%20/g, ' '));
    if ($(this).attr('data-muun-headline') != 'null') {
      $('.member-popup__headline').text($(this).attr('data-muun-headline').replace(/%20/g, ' '));
    }
    if ($(this).attr('data-muun-bio') != 'null') {
      $('.member-popup__bio').text($(this).attr('data-muun-bio').replace(/%20/g, ' '));
    }
    expertises_content = "";
    if ($(this).attr('data-muun-expertises') != 'null') {
      $(this).attr('data-muun-expertises').split(',').forEach(function(entry) {
        expertises_content += "<span class='member-popup__expertise'>"+ entry.toLowerCase() +"</span>";
      });
    }
    $('.member-popup__expertises').html(expertises_content);
    urls_content = "";
    if ($(this).attr('data-muun-email') != 'null') {
      urls_content += "<a href='mailto:"+ $(this).attr('data-muun-email') +"' class='member-popup__url member-popup__email'>email</a>";
    }
    if ($(this).attr('data-muun-website') != 'null') {
      urls_content += "<a href="+ $(this).attr('data-muun-website') +" target='_blank' class='member-popup__url member-popup__website'>website</a>";
    }
    if ($(this).attr('data-muun-twitter') != 'null') {
      urls_content += "<a href="+ $(this).attr('data-muun-twitter') +" target='_blank' class='member-popup__url member-popup__twitter'>twitter</a>";
    }
    if ($(this).attr('data-muun-facebook') != 'null') {
      urls_content += "<a href="+ $(this).attr('data-muun-facebook') +" target='_blank' class='member-popup__url member-popup__facebook'>facebook</a>";
    }
    if ($(this).attr('data-muun-instagram') != 'null') {
      urls_content += "<a href="+ $(this).attr('data-muun-instagram') +" target='_blank' class='member-popup__url member-popup__instagram'>instagram</a>";
    }
    if ($(this).attr('data-muun-linkedin') != 'null') {
      urls_content += "<a href="+ $(this).attr('data-muun-linkedin') +" target='_blank' class='member-popup__url member-popup__linkedin'>linkedin</a>";
    }
    $('.member-popup__urls').html(urls_content);
  });

  $('.muun-members-popup-overlay').on('click', function() {
    $('.muun-members-popup, .muun-members-popup-overlay').fadeOut(300);
    $('.muun-members-popup__container').remove();
    return false;
  });
});
