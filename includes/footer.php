    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> Banque PDO — Application sécurisée</p>
    </footer>
    <script>
        // Bouton hamburger pour la navigation mobile
        const toggle = document.getElementById('navToggle');
        const nav    = document.getElementById('mainNav');
        if (toggle && nav) {
            toggle.addEventListener('click', function () {
                const isOpen = nav.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen);
            });
        }
    </script>
</body>
</html>