$(window).load(function () {
    $('.portfolio_menu ul li').click(function () {
        $('.portfolio_menu ul li').removeClass('active_prot_menu');
        $(this).addClass('active_prot_menu');
    });

    var $container = $('#portfolio');
    $container.isotope({
        itemSelector: '.col-sm-4',
        layoutMode: 'fitRows'
    });
    $('#filters').on('click', 'a', function () {
        var filterValue = $(this).attr('data-filter');
        $container.isotope({ filter: filterValue });
        return false;
    });
});