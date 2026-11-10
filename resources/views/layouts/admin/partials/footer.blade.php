<footer class="container-fluid d-flex d-print-none align-items-center justify-content-center justify-content-sm-between flex-wrap py-3 ms-0 bg-white">
    <p class="mb-0 me-3 text-center"> {{ get_option('general')['copy_right'] ?? '' }}</p>
    <p class="mb-0">{{ get_option('general')['footer_text'] ?? '' }}: <a class='footer-link' href="{{ get_option('general')['footer_link'] ?? '' }}" target="_blank"> {{ get_option('general')['footer_link_text'] ?? '' }}</a></p>
</footer>
