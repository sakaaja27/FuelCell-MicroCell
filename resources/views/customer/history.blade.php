<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - MicroCell</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#041706] font-sans text-slate-200">
    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-[#041706]/80 backdrop-blur-md border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-emerald-400 font-bold text-xl tracking-tight hover:text-emerald-300 transition-colors">
                <img src="/assets/images/logo.png" alt="MicroCell Logo" class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-110" />
                <span class="font-sans text-xl font-extrabold text-white tracking-tight">
                    Micro<span class="text-emerald-400">Cell</span>
                </span>
            </a>
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-emerald-100/70 hover:text-white transition-colors">Beranda</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-red-400 hover:text-red-300 transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="pt-32 pb-24 max-w-5xl mx-auto px-6 min-h-screen">
        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-white mb-2">Riwayat Transaksi</h1>
            <p class="text-emerald-100/70">Pantau status pesanan MicroCell Anda di sini.</p>
        </div>

        @if (session('success'))
            <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center gap-3 text-emerald-300">
                <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-8 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl flex items-center gap-3 text-red-300">
                <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
        @endif
        
        @php
            $subscriptions = Auth::user()->customer ? Auth::user()->customer->subscriptions()->where('status', 'Aktif')->get() : collect();
        @endphp

        @if($subscriptions->count() > 0)
        <div class="mb-10">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="activity" class="w-5 h-5 text-emerald-400"></i> Layanan Aktif (Sewa)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($subscriptions as $sub)
                    <a href="{{ route('customer.checkout', ['schema' => $sub->schema_id, 'subscription_id' => $sub->id]) }}" class="block group {{ $sub->status === 'Selesai' ? 'pointer-events-none' : '' }}">
                        <div class="bg-emerald-900/40 border {{ $sub->status === 'Selesai' ? 'border-blue-700/50' : 'border-emerald-700/50' }} rounded-2xl p-5 relative overflow-hidden transition-all duration-300 group-hover:bg-emerald-800/50 group-hover:border-emerald-500/70 group-hover:shadow-[0_0_20px_rgba(16,185,129,0.2)]">
                            <div class="absolute top-0 right-0 w-20 h-20 {{ $sub->status === 'Selesai' ? 'bg-blue-500/10' : 'bg-emerald-500/10' }} blur-2xl rounded-full transition-transform duration-300 group-hover:scale-150"></div>
                            
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="text-lg font-bold text-white group-hover:text-emerald-300 transition-colors">{{ $sub->schema->skema ?? 'Layanan' }}</h3>
                                @if($sub->status === 'Selesai')
                                    <div class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm">Lunas</div>
                                @else
                                    <div class="bg-emerald-500 text-emerald-950 text-xs font-bold px-2 py-1 rounded-lg shadow-sm">Bayar Tagihan</div>
                                @endif
                            </div>
                            
                            <p class="text-sm text-emerald-200/70 mb-3">Mulai: {{ \Carbon\Carbon::parse($sub->started_at)->format('d M Y') }}</p>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-emerald-100/60">Progress Kepemilikan</span>
                                    <span class="font-bold text-emerald-400">{{ $sub->paid_months }} / {{ $sub->total_months }} Bulan</span>
                                </div>
                                <div class="w-full bg-emerald-950/50 rounded-full h-1.5">
                                    @php
                                        $percentage = min(100, ($sub->paid_months / max(1, $sub->total_months)) * 100);
                                    @endphp
                                    <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between border-t border-emerald-800/50 pt-3">
                                <span class="text-sm text-emerald-100/60">Jatuh Tempo Berikutnya</span>
                                @if($sub->status === 'Selesai')
                                    <span class="text-sm font-bold text-blue-400">Kepemilikan Penuh</span>
                                @else
                                    <span class="text-sm font-bold {{ \Carbon\Carbon::parse($sub->next_billing_date)->isPast() ? 'text-red-400' : 'text-emerald-400' }}">
                                        {{ \Carbon\Carbon::parse($sub->next_billing_date)->format('d M Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="space-y-6">
            @forelse ($orders as $order)
                @php
                    $relCustomer = $order->customer_id ? \App\Models\Customer::find($order->customer_id) : (Auth::user()->customer ?? null);
                    $customerPhone = $relCustomer->phone ?? (Auth::user()->customer->phone ?? '-');
                    $customerEmail = $relCustomer->email ?? (Auth::user()->customer->email ?? Auth::user()->email);
                    $paymentName = $order->paymentMethod->nama ?? 'Transfer Bank';
                    $paymentDetail = $order->paymentMethod ? ($order->paymentMethod->jenis . ' - ' . $order->paymentMethod->va) : '-';
                @endphp
                <div onclick="openOrderCustomerModal(this)"
                    data-id="{{ $order->id }}"
                    data-skema="{{ $order->skema }}"
                    data-qty="{{ $order->qty }}"
                    data-duration="{{ $order->duration ?? '' }}"
                    data-total="Rp {{ number_format($order->total, 0, ',', '.') }}"
                    data-status="{{ $order->status }}"
                    data-tanggal="{{ \Carbon\Carbon::parse($order->created_at)->format('d F Y, H:i') }}"
                    data-customer="{{ $order->customer }}"
                    data-phone="{{ $customerPhone }}"
                    data-email="{{ $customerEmail }}"
                    data-alamat="{{ $order->alamat ?? 'Tidak ada alamat tercatat' }}"
                    data-payment="{{ $paymentName }}"
                    data-payment-detail="{{ $paymentDetail }}"
                    data-preorder="{{ $order->is_preorder ? 'Ya (Pre-Order)' : 'Bukan' }}"
                    data-invoice="{{ in_array($order->status, ['Proses', 'Selesai']) ? route('customer.invoice', $order->id) : '' }}"
                    class="group cursor-pointer bg-[#0A2F1D] border border-emerald-800/50 rounded-[24px] p-6 lg:p-8 shadow-xl flex flex-col md:flex-row gap-6 items-start md:items-center transition-all hover:border-emerald-500 hover:bg-[#0c3823] hover:shadow-[0_0_25px_rgba(16,185,129,0.15)]">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2.5 py-1 rounded-md border border-emerald-500/20 group-hover:border-emerald-400/40 transition-colors">{{ $order->id }}</span>
                            <span class="text-xs text-emerald-100/50">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}</span>
                            <span class="text-xs text-emerald-400/70 ml-auto md:ml-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="info" class="w-3.5 h-3.5"></i> Klik untuk detail
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-1 group-hover:text-emerald-300 transition-colors">{{ $order->skema }}</h3>
                        <p class="text-sm text-emerald-200/70">{{ $order->qty }} Unit &bull; Menggunakan {{ $order->paymentMethod->nama ?? 'Transfer' }}</p>
                        @if($order->alamat)
                            <p class="text-xs text-emerald-100/50 mt-2 line-clamp-1 flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0"></i> {{ $order->alamat }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="flex-shrink-0 md:text-right flex flex-col items-start md:items-end w-full md:w-auto">
                        <div class="text-sm text-emerald-100/60 mb-1">Total Pembayaran</div>
                        <div class="text-xl font-black text-emerald-300 mb-3">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                        
                        @php
                            $statusColor = match($order->status) {
                                'Menunggu' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                'Proses' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                'Selesai' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                'Dibatalkan' => 'bg-red-500/20 text-red-300 border-red-500/30',
                                default => 'bg-slate-500/20 text-slate-300 border-slate-500/30'
                            };
                            $statusIcon = match($order->status) {
                                'Menunggu' => 'clock',
                                'Proses' => 'loader',
                                'Selesai' => 'check-circle',
                                'Dibatalkan' => 'x-circle',
                                default => 'circle'
                            };
                        @endphp
                        
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold {{ $statusColor }}">
                                <i data-lucide="{{ $statusIcon }}" class="w-3.5 h-3.5"></i> {{ $order->status }}
                            </div>

                            @if(in_array($order->status, ['Proses', 'Selesai']))
                                <a href="{{ route('customer.invoice', $order->id) }}" onclick="event.stopPropagation()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-500 hover:text-emerald-950 text-xs font-bold transition-all shadow-sm">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Unduh Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-[#0A2F1D] border border-emerald-800/50 rounded-[32px]">
                    <div class="w-16 h-16 bg-emerald-900/50 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-500">
                        <i data-lucide="shopping-bag" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Belum ada transaksi</h3>
                    <p class="text-emerald-100/60 max-w-sm mx-auto mb-6">Anda belum pernah melakukan pemesanan. Jelajahi layanan kami di halaman utama.</p>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center bg-emerald-500 text-emerald-950 font-bold px-6 py-3 rounded-xl hover:bg-emerald-400 transition-colors">
                        Lihat Layanan
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Detail Transaksi Customer -->
    <div id="customerDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#0A2F1D] border border-emerald-700/60 rounded-[28px] shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] text-slate-200 animate-in fade-in zoom-in-95 duration-200">
            <!-- Header Modal -->
            <div class="p-6 border-b border-emerald-800/60 flex items-center justify-between bg-[#041706]/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Detail Transaksi</h2>
                        <p class="text-xs text-emerald-200/60" id="custModalOrderId">-</p>
                    </div>
                </div>
                <button onclick="closeCustomerModal()" class="text-emerald-100/60 hover:text-white p-2 hover:bg-emerald-900/40 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 overflow-y-auto space-y-6">
                <!-- Status & Ringkasan -->
                <div class="flex items-center justify-between p-4 bg-[#041706]/70 rounded-2xl border border-emerald-900/40">
                    <div>
                        <div class="text-xs text-emerald-100/50 mb-0.5">Status Pesanan</div>
                        <div id="custModalStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold mt-1">
                            -
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-emerald-100/50 mb-0.5">Total Pembayaran</div>
                        <div class="text-xl font-black text-emerald-300" id="custModalTotal">-</div>
                    </div>
                </div>

                <!-- Informasi Pelanggan -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-emerald-400 tracking-wider uppercase flex items-center gap-1.5">
                        <i data-lucide="user" class="w-3.5 h-3.5"></i> Data Pemesan
                    </h3>
                    <div class="bg-[#041706]/40 rounded-2xl p-4 border border-emerald-900/30 space-y-2.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Nama Pelanggan</span>
                            <span class="font-semibold text-white" id="custModalCustomer">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">No. WhatsApp / Telp</span>
                            <span class="font-semibold text-emerald-300 flex items-center gap-1.5" id="custModalPhoneWrapper">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span id="custModalPhone">-</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Email</span>
                            <span class="font-semibold text-white" id="custModalEmail">-</span>
                        </div>
                    </div>
                </div>

                <!-- Alamat Pengiriman / Pemasangan -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-emerald-400 tracking-wider uppercase flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Alamat Pengiriman / Lokasi
                    </h3>
                    <div class="bg-[#041706]/40 rounded-2xl p-4 border border-emerald-900/30 text-sm">
                        <p class="text-slate-200 leading-relaxed" id="custModalAlamat">-</p>
                    </div>
                </div>

                <!-- Detail Produk & Transaksi -->
                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-emerald-400 tracking-wider uppercase flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i> Rincian Paket & Pembayaran
                    </h3>
                    <div class="bg-[#041706]/40 rounded-2xl p-4 border border-emerald-900/30 space-y-2.5 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Paket Layanan</span>
                            <span class="font-semibold text-white" id="custModalSkema">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Jumlah Unit</span>
                            <span class="font-semibold text-white" id="custModalQty">-</span>
                        </div>
                        <div class="flex justify-between items-center" id="custModalDurationRow">
                            <span class="text-emerald-100/60">Durasi Kontrak Sewa</span>
                            <span class="font-semibold text-white" id="custModalDuration">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Metode Pembayaran</span>
                            <span class="font-semibold text-white" id="custModalPayment">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Akun Pembayaran</span>
                            <span class="text-emerald-300 font-mono text-xs" id="custModalPaymentDetail">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-100/60">Waktu Pemesanan</span>
                            <span class="text-emerald-100/80 text-xs" id="custModalTanggal">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-6 border-t border-emerald-800/60 flex items-center justify-between gap-3 bg-[#041706]/60">
                <a href="#" id="custModalInvoiceBtn" class="hidden items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-emerald-950 font-bold text-sm transition-all shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Unduh Invoice PDF
                </a>
                <button type="button" onclick="closeCustomerModal()" class="ml-auto px-5 py-2.5 text-sm font-bold text-emerald-200/80 bg-emerald-900/40 hover:bg-emerald-900/70 border border-emerald-700/50 rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openOrderCustomerModal(card) {
            const data = card.dataset;
            document.getElementById('custModalOrderId').textContent = 'Order #' + data.id;
            document.getElementById('custModalTotal').textContent = data.total;
            document.getElementById('custModalCustomer').textContent = data.customer;
            document.getElementById('custModalPhone').textContent = data.phone;
            document.getElementById('custModalEmail').textContent = data.email;
            document.getElementById('custModalAlamat').textContent = data.alamat;
            document.getElementById('custModalSkema').textContent = data.skema;
            const isAfterSales = data.skema.toLowerCase().includes('after-sales') || data.skema.toLowerCase().includes('after sales') || data.skema.toLowerCase().includes('instalasi') || data.skema.toLowerCase().includes('servis');
            document.getElementById('custModalQty').textContent = isAfterSales ? '1 Kunjungan Servis' : (data.qty + ' Unit');
            
            const durationRow = document.getElementById('custModalDurationRow');
            if (data.duration && data.duration.trim() !== '') {
                durationRow.classList.remove('hidden');
                document.getElementById('custModalDuration').textContent = data.duration + ' Bulan';
            } else {
                durationRow.classList.add('hidden');
            }

            document.getElementById('custModalPayment').textContent = data.payment;
            document.getElementById('custModalPaymentDetail').textContent = data.paymentDetail;
            document.getElementById('custModalTanggal').textContent = data.tanggal;

            // Status Badge
            const statusBadge = document.getElementById('custModalStatusBadge');
            let colorClass = 'bg-slate-500/20 text-slate-300 border-slate-500/30';
            let iconName = 'circle';
            if (data.status === 'Menunggu') {
                colorClass = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
                iconName = 'clock';
            } else if (data.status === 'Proses') {
                colorClass = 'bg-blue-500/20 text-blue-300 border-blue-500/30';
                iconName = 'loader';
            } else if (data.status === 'Selesai') {
                colorClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
                iconName = 'check-circle';
            } else if (data.status === 'Dibatalkan') {
                colorClass = 'bg-red-500/20 text-red-300 border-red-500/30';
                iconName = 'x-circle';
            }
            statusBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-bold mt-1 ' + colorClass;
            statusBadge.innerHTML = `<i data-lucide="${iconName}" class="w-3.5 h-3.5"></i> ${data.status}`;

            // Invoice Button
            const invoiceBtn = document.getElementById('custModalInvoiceBtn');
            if (data.invoice && data.invoice.trim() !== '') {
                invoiceBtn.href = data.invoice;
                invoiceBtn.classList.remove('hidden');
                invoiceBtn.classList.add('inline-flex');
            } else {
                invoiceBtn.classList.add('hidden');
                invoiceBtn.classList.remove('inline-flex');
            }

            const modal = document.getElementById('customerDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            lucide.createIcons();
        }

        function closeCustomerModal() {
            const modal = document.getElementById('customerDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Close on backdrop click
        document.getElementById('customerDetailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCustomerModal();
            }
        });
    </script>
</body>
</html>
