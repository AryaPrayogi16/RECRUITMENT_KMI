<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<button type="button" onclick="confirmLogout()" {{ $attributes->merge(['class' => 'logout-btn']) }}>
    <i class="fas fa-sign-out-alt"></i>
    <span>{{ $slot->isEmpty() ? 'Logout' : $slot }}</span>
</button>

<script>
function confirmLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        document.getElementById('logout-form').submit();
    }
}
</script>