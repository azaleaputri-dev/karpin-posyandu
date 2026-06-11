@if ($paginator->hasPages())
    <div class="flex flex-col gap-4 border-t border-slate-50 pt-6 text-[11px] font-black uppercase tracking-widest text-slate-400 sm:flex-row sm:items-center sm:justify-between">
        <div>
            Showing <span class="text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span> to <span class="text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span> of <span class="text-slate-800">{{ $paginator->total() }}</span> results
        </div>
        <div class="pagination-links">
            {{ $paginator->links() }}
        </div>
    </div>
@endif
