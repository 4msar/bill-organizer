import { Note } from '@/types/model';

export type ExportFormat = 'json' | 'markdown' | 'csv' | 'txt';

export function useNoteExport() {
    /**
     * Export a single note in the specified format
     */
    function exportNote(note: Note, format: ExportFormat = 'markdown') {
        let content = '';
        let filename = '';
        let mimeType = '';

        switch (format) {
            case 'json':
                content = JSON.stringify(note, null, 2);
                filename = `${sanitizeFilename(note.title || 'note')}.json`;
                mimeType = 'application/json';
                break;

            case 'markdown':
                content = generateMarkdown(note);
                filename = `${sanitizeFilename(note.title || 'note')}.md`;
                mimeType = 'text/markdown';
                break;

            case 'csv':
                content = generateCSV([note]);
                filename = `${sanitizeFilename(note.title || 'note')}.csv`;
                mimeType = 'text/csv';
                break;

            case 'txt':
                content = generateTextFile(note);
                filename = `${sanitizeFilename(note.title || 'note')}.txt`;
                mimeType = 'text/plain';
                break;
        }

        downloadFile(content, filename, mimeType);
    }

    /**
     * Export multiple notes in the specified format
     */
    function exportNotes(notes: Note[], format: ExportFormat = 'json') {
        let content = '';
        let filename = '';
        let mimeType = '';

        switch (format) {
            case 'json':
                content = JSON.stringify(notes, null, 2);
                filename = `notes-export-${getDateString()}.json`;
                mimeType = 'application/json';
                break;

            case 'markdown':
                content = notes.map(generateMarkdown).join('\n\n---\n\n');
                filename = `notes-export-${getDateString()}.md`;
                mimeType = 'text/markdown';
                break;

            case 'csv':
                content = generateCSV(notes);
                filename = `notes-export-${getDateString()}.csv`;
                mimeType = 'text/csv';
                break;

            case 'txt':
                content = notes.map(generateTextFile).join('\n\n' + '='.repeat(80) + '\n\n');
                filename = `notes-export-${getDateString()}.txt`;
                mimeType = 'text/plain';
                break;
        }

        downloadFile(content, filename, mimeType);
    }

    /**
     * Generate markdown format for a single note
     */
    function generateMarkdown(note: Note): string {
        let md = `# ${note.title || 'Untitled Note'}\n\n`;
        md += `**Created:** ${new Date(note.created_at).toLocaleString()}\n`;
        md += `**Updated:** ${new Date(note.updated_at).toLocaleString()}\n`;
        md += `**Pinned:** ${note.is_pinned ? 'Yes' : 'No'}\n`;

        if (note.team) {
            md += `**Team:** ${note.team.name}\n`;
        }

        if (note.related && note.related.length > 0) {
            md += `**Related Items:** ${note.related.length}\n`;
        }

        md += `\n---\n\n`;
        md += note.content || '*No content*';

        if (note.related && note.related.length > 0) {
            md += `\n\n## Related Items\n\n`;
            note.related.forEach((item) => {
                md += `- ${item.type}: ${item.notable?.title || item.notable_id}\n`;
            });
        }

        return md;
    }

    /**
     * Generate CSV format for notes
     */
    function generateCSV(notes: Note[]): string {
        const headers = ['Title', 'Content', 'Created', 'Updated', 'Pinned', 'Team', 'Related Items'];
        const rows = notes.map((note) => {
            return [
                escapeCsvValue(note.title || 'Untitled'),
                escapeCsvValue(note.content || ''),
                new Date(note.created_at).toLocaleString(),
                new Date(note.updated_at).toLocaleString(),
                note.is_pinned ? 'Yes' : 'No',
                note.team?.name || '',
                note.related?.length || 0,
            ];
        });

        const csvContent = [headers, ...rows].map((row) => row.join(',')).join('\n');
        return csvContent;
    }

    /**
     * Generate plain text format for a single note
     */
    function generateTextFile(note: Note): string {
        let text = `${note.title || 'Untitled Note'}\n`;
        text += '='.repeat(Math.max(note.title?.length || 0, 14)) + '\n\n';
        text += `Created: ${new Date(note.created_at).toLocaleString()}\n`;
        text += `Updated: ${new Date(note.updated_at).toLocaleString()}\n`;
        text += `Pinned: ${note.is_pinned ? 'Yes' : 'No'}\n`;

        if (note.team) {
            text += `Team: ${note.team.name}\n`;
        }

        if (note.related && note.related.length > 0) {
            text += `Related Items: ${note.related.length}\n`;
        }

        text += '\n' + '-'.repeat(80) + '\n\n';
        text += note.content || '*No content*';

        if (note.related && note.related.length > 0) {
            text += '\n\nRelated Items:\n';
            note.related.forEach((item) => {
                text += `- ${item.type}: ${item.notable?.title || item.notable_id}\n`;
            });
        }

        return text;
    }

    /**
     * Escape CSV values to handle commas, quotes, and newlines
     */
    function escapeCsvValue(value: string): string {
        if (!value) return '""';

        // If value contains comma, quote, or newline, wrap in quotes and escape existing quotes
        if (value.includes(',') || value.includes('"') || value.includes('\n')) {
            return `"${value.replace(/"/g, '""')}"`;
        }

        return value;
    }

    /**
     * Sanitize filename by removing invalid characters
     * Allows alphanumeric, spaces, dots, hyphens, and underscores
     * Removes path separators and other dangerous characters
     */
    function sanitizeFilename(filename: string): string {
        return filename
            .replace(/[/\\:*?"<>|]/g, '') // Remove dangerous characters
            .replace(/\s+/g, ' ') // Normalize whitespace
            .trim()
            .substring(0, 100);
    }

    /**
     * Get current date string for filename
     */
    function getDateString(): string {
        return new Date().toISOString().split('T')[0];
    }

    /**
     * Download file to browser
     */
    function downloadFile(content: string, filename: string, mimeType: string) {
        try {
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Failed to download file:', error);
            // Fallback: try to open content in new window
            const text = `Failed to download file. Error: ${error instanceof Error ? error.message : 'Unknown error'}`;
            alert(text);
        }
    }

    return {
        exportNote,
        exportNotes,
    };
}
