<?php
    session_destroy();
    session_abort();
    session_unset();
echo'
<script>
    window.location.href = "login.html"
</script> ';