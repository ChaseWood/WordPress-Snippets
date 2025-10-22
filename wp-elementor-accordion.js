// Found this fix for the accordion widgets default open behavior and it works perfectly 
// Thank you Element.how https://element.how/elementor-accordion-scroll-to-top/
<script>
/* Code from https://element.how/elementor-accordion-scroll-to-top/
 * Copyright 2023 Element.How
 */
 
document.addEventListener("DOMContentLoaded", function () {

    let accordionTitles = document.querySelectorAll('.e-n-accordion-item-title, .elementor-widget-accordion .elementor-tab-title');

    accordionTitles.forEach(title => {
        title.addEventListener('click', function (event) {
            
            document.documentElement.style.scrollBehavior = 'auto';
            setTimeout(() => {
                document.documentElement.style.removeProperty('scroll-behavior');
            }, 700);

            let initialTopOffset = title.getBoundingClientRect().top;

            let startTime;

            function scrollAnimation(currentTime) {
                if (!startTime) startTime = currentTime;

                let elapsedTime = currentTime - startTime;

                if (elapsedTime < 600) { /* 0.6s stabilization duration */
                    let currentTopOffset = title.getBoundingClientRect().top;
                    let offset = currentTopOffset - initialTopOffset;
                    window.scrollBy(0, offset);
                    requestAnimationFrame(scrollAnimation);
                }
            }

            requestAnimationFrame(scrollAnimation);
        });
    });
});
</script>
