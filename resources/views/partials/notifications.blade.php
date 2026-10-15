<div class="notice-stack" aria-label="Notifikasi sistem">
    @if(session('success'))
        <x-notification type="success" title="Berhasil" :messages="session('success')" :auto-dismiss="true" />
    @endif
    @if(session('status'))
        <x-notification type="success" title="Berhasil" :messages="session('status')" :auto-dismiss="true" />
    @endif
    @if(session('warning'))
        <x-notification type="warning" :messages="session('warning')" />
    @endif
    @if(session('info'))
        <x-notification type="info" :messages="session('info')" />
    @endif
    @if(session('error'))
        <x-notification type="error" :messages="session('error')" />
    @endif
    @if($errors->any())
        <x-notification type="error" title="Data belum dapat diproses" :messages="$errors->all()" />
    @endif
</div>
