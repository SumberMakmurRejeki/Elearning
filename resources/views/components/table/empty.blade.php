<tr>
    <td colspan="{{ $colspan ?? 1 }}" class="px-6 py-12">
        <x-empty-state
            :title="$title ?? 'Belum ada data'"
            :description="$description ?? 'Data akan tampil di sini setelah tersedia.'"
            :action-label="$actionLabel ?? null"
            :action-href="$actionHref ?? null"
        />
    </td>
</tr>
