<?php
// includes/footer.php
?>
<footer class="footer mt-5" style="background: #f7e7d0; color: #7a5a2b; text-align: center; padding: 2rem 0 1.2rem 0; font-size: 1.05rem; font-family: 'Inter', system-ui, sans-serif; margin-top: 3rem; border: none; box-shadow: none;">
    <p style="margin:0; font-weight:500; letter-spacing:0.5px;">
        &copy; <span id="footerYear"></span> Pet Adoption Portal. All rights reserved.
    </p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/pet_adoption_system/assets/js/script.js"></script>
<script>
document.getElementById('footerYear').textContent = new Date().getFullYear();
</script>

</body>
</html>
