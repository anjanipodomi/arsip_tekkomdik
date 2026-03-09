document.addEventListener("DOMContentLoaded", function() {

    function loadNotifCount(){
        fetch(BASE_URL + 'app/ajax/get_notif_count.php')
        .then(res => res.text())
        .then(total => {

            total = parseInt(total);

            const badgeNavbar = document.querySelector('.notif-badge');
            const badgeSidebar = document.querySelector('.sidebar-badge');

            if(total > 0){
                if(badgeNavbar){
                    badgeNavbar.innerText = total;
                }
                if(badgeSidebar){
                    badgeSidebar.innerText = total;
                }
            }
        });
    }

    loadNotifCount();
    setInterval(loadNotifCount, 5000);

});