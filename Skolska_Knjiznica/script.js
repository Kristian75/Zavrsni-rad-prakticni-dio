    document.querySelectorAll("section").forEach(section => {
        section.style.opacity = 0;
        section.style.transform = "translateY(30px)";
        setTimeout(() => {
            section.style.transition = "all 1.2s ease";
            section.style.opacity = 1;
            section.style.transform = "translateY(0)";
        }, 300);
    });

    document.querySelectorAll("nav ul li a").forEach(link => {
        link.addEventListener("click", function (event) {
            if (this.getAttribute("href").includes(".html")) {
                event.preventDefault();
                document.body.style.opacity = 0;
                setTimeout(() => {
                    window.location.href = this.getAttribute("href");
                }, 500);
            }
        });
    });

     
    
    function closeLightbox() {
        const lightbox = document.getElementById("lightbox");
        lightbox.style.display = "none";
        document.getElementById("lightbox-img").src = "";
    }