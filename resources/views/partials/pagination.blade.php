@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <div>
            Menampilkan {{ $paginator->firstItem() ?? 0 }} sampai {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </div>
        <div>
            {{ $paginator->links() }}
        </div>
    </div>
@endif
