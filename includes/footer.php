            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <?php if (isset($_SESSION['success'])): ?>
        <script>alert('<?php echo addslashes($_SESSION['success']); ?>');</script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <script>alert('<?php echo addslashes($_SESSION['error']); ?>');</script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</body>
</html>
