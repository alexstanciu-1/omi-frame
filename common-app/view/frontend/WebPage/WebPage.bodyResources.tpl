<div style="position: relative;  left: 0px; z-index: 100000; bottom: 20%; background-color: white;"><?= $this->sync_output ?></div>

<script src="<?= Q_VIEW_RES ?>main/js/trumbowyg/js/trumbowyg.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.js" integrity="sha512-zO8oeHCxetPn1Hd9PdDleg5Tw1bAaP0YmNvPY8CwcRyUk7d7/+nyElmFrB6f7vg4f7Fv4sui1mcep8RIEShczg==" crossorigin="anonymous"></script> -->
<!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/selectable.js@latest/selectable.min.js"></script> -->
<script src="https://www.google.com/recaptcha/api.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/tyippy/js/popper.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/tyippy/js/tippy-bundle.umd.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/splide/js/splide.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/wNumb.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/nouislider/nouislider.all.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/fancybox/fancybox.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/readmore.min.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/custom_selectable.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/functions.js"></script>
<script type="text/javascript" src="<?= Q_VIEW_RES ?>main/js/flatpickr/flatpickr.min.js"></script>

@if (($_T___INF_LANG = q_get_lang()))
    <script type="text/javascript" src="lang/js_<?= $_T___INF_LANG ?>.js"></script>
@endif