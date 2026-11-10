
(function ($) {

    "use strict";

    function sideManu() {
 
        $(".woodland-sidebar-open, .sidebar-close-btn").on("click ", function() {
            $(".woodland-sidebar, .woodland-main-section").toggleClass("sidebar-active");
        });

        $("li>ul").toggleClass("dropdown-menu");

        let animationSpeed = 300;

        let subMenuSelector = ".dropdown-menu";

        $('.woodland-sidebar-menu > ul').on('click', '.dropdown a', function(e) {
            let $this = $(this);
            let checkElement = $this.next();

            if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
                checkElement.slideUp(animationSpeed, function() {
                    checkElement.removeClass('menu-open');
                });
                checkElement.parent("li").removeClass("active");
            }

            //If the menu is not visible
            else if ((checkElement.is(subMenuSelector)) && (!checkElement.is(':visible'))) {
                //Get the parent menu
                let parent = $this.parents('ul').first();
                //Close all open menus within the parent
                let ul = parent.find('ul:visible').slideUp(animationSpeed);
                //Remove the menu-open class from the parent
                ul.removeClass('menu-open');
                //Get the parent li
                let parent_li = $this.parent("li");

                //Open the target menu and add the menu-open class
                checkElement.slideDown(animationSpeed, function() {
                    //Add the class active to the parent li
                    checkElement.addClass('menu-open');
                    parent.find('li.active').removeClass('active');
                    parent_li.addClass('active');
                });
            }
            //if this isn't a link, prevent the page from being redirected
            if (checkElement.is(subMenuSelector)) {
                e.preventDefault();
            }
        });
    }
    sideManu()


    $('.select-dropdown').niceSelect();

    // counterup 
    $('.counter').counterUp({
        delay: 10,
        time: 3000, 
    });




        //Use the id of the form instead of #change
        $('.select-dropdown').change(function(){
            var title = $(this).val();
            if(title == "reject"){
                $('#reject-modal').modal('show');
            } else if(title == "approved"){
                $('#approved-modal').modal('show');
            }
        });


        $(document).ready(function() {
            $('#summernote').summernote();
        });


        $('.input-images').imageUploader({
            imagesInputName: 'images',
            preloadedInputName: 'old_images',
            maxSize: 1024 * 10240,
            maxFiles: 4
        });


        // photo upload preview
        function readURL(input) {
            if (input.files && input.files[0]) {
              var reader = new FileReader();
              reader.onload = function(e) {
                $('.image-preview').attr('src', e.target.result);
                $('.image-preview').hide();
                $('.image-preview').fadeIn(650);
                $('.image-preview').addClass('active');
              }
              reader.readAsDataURL(input.files[0]);
            }
          }
          
          $("#file-input-upload").change(function() {
            readURL(this);
            $('.image-preview-icon').addClass('d-none');
          });
          



        //   image upload tow js
        jQuery(document).ready(function () {
            ImgUpload();
          });
          function ImgUpload() {
            var imgWrap = "";
            var imgArray = [];
          
            $('.upload__inputfile').each(function () {
              $(this).on('change', function (e) {
                imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
                var maxLength = $(this).attr('data-max_length');
                var files = e.target.files;
                var filesArr = Array.prototype.slice.call(files);
                var iterator = 0;
                filesArr.forEach(function (f, index) {
          
                  if (!f.type.match('image.*')) {
                    return;
                  }
          
                  if (imgArray.length > maxLength) {
                    return false
                  } else {
                    var len = 0;
                    for (var i = 0; i < imgArray.length; i++) {
                      if (imgArray[i] !== undefined) {
                        len++;
                      }
                    }
                    if (len > maxLength) {
                      return false;
                    } else {
                      imgArray.push(f);
          
                      var reader = new FileReader();
                      reader.onload = function (e) {
                        var html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-number='" + $(".upload__img-close").length + "' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                        imgWrap.append(html);
                        imgWrap.addClass('img-active');
                        iterator++;
                      }
                      reader.readAsDataURL(f);
                    }
                  }
                });
              });
            });
          
            $('body').on('click', ".upload__img-close", function (e) {
              var file = $(this).parent().data("file");
              for (var i = 0; i < imgArray.length; i++) {
                if (imgArray[i].name === file) {
                  imgArray.splice(i, 1);
                  break;
                }
              }
              $(this).parent().parent().remove();
            });
          }

          
          $(document).ready(function () {
            let showPass = document.querySelector('.current-pass');
            showPass.addEventListener('click', function() {
                showPass.classList.toggle("show-pass");
                var x = document.getElementById("password");
                if (x.type === "password") {
                    x.type = "text";
                } else {
                    x.type = "password";
                }
            })

            let newPass = document.querySelector('.new-pass');
            newPass.addEventListener('click', function() {
                newPass.classList.toggle("show-pass");
                var x = document.getElementById("password1");
                if (x.type === "password") {
                    x.type = "text";
                } else {
                    x.type = "password";
                }
            })

            let newPass2 = document.querySelector('.confirm-new-pass');
            newPass2.addEventListener('click', function() {
                newPass2.classList.toggle("show-pass");
                var x = document.getElementById("password2");
                if (x.type === "password") {
                    x.type = "text";
                } else {
                    x.type = "password";
                }
            })
        });




    
})(jQuery);

