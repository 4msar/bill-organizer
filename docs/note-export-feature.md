# Note Export Functionality

## Overview
This document describes the note export functionality added to the Bill Organizer application.

## Features
The export functionality allows users to export their notes in multiple formats:
- **JSON**: Complete note data including metadata
- **Markdown**: Human-readable format with proper formatting
- **CSV**: Spreadsheet format for bulk data analysis
- **Text**: Plain text format for simple viewing

## Export Options

### 1. Export All Notes (Bulk Export)
Located in the Notes Index page header, next to the Filter and New Note buttons.

**How to use:**
1. Navigate to the Notes page
2. Click the "Export Notes" button (only visible when notes exist)
3. Select your desired format from the dropdown menu
4. The file will be downloaded automatically to your browser's download folder

**File naming:**
- `notes-export-YYYY-MM-DD.json`
- `notes-export-YYYY-MM-DD.md`
- `notes-export-YYYY-MM-DD.csv`
- `notes-export-YYYY-MM-DD.txt`

### 2. Export Individual Note from Card
Located in the dropdown menu (three dots) on each note card.

**How to use:**
1. Click the three-dot menu on any note card
2. Hover over "Export" to see format options
3. Select your desired format
4. The file will be downloaded with the note's title as the filename

### 3. Export Individual Note from View Dialog
Located in the note view dialog footer, next to Edit, Pin, and Delete buttons.

**How to use:**
1. Click "View" on any note card
2. In the dialog, click the "Export" button
3. Select your desired format from the dropdown
4. The file will be downloaded with the note's title as the filename

## Export Formats Detail

### JSON Format
- Contains complete note object including:
  - id, title, content
  - created_at, updated_at timestamps
  - is_pinned status
  - user_id, team_id
  - related items (if any)
- Formatted with 2-space indentation
- Best for: Data backup, migration, programmatic use

### Markdown Format
- Header with note title (# heading)
- Metadata section with creation date, update date, pinned status, team name
- Separator line
- Note content (preserves original markdown)
- Related items section (if any)
- Best for: Documentation, sharing, version control

### CSV Format
- Columns: Title, Content, Created, Updated, Pinned, Team, Related Items
- Values properly escaped for commas and quotes
- Best for: Spreadsheet analysis, data processing

### Text Format
- Title with underline
- Metadata section
- Content separator
- Note content
- Related items section (if any)
- Best for: Simple viewing, printing

## Technical Implementation

### File Structure
```
resources/js/composables/useNoteExport.ts - Main export composable
resources/js/pages/Notes/Index.vue - Bulk export integration
resources/js/components/notes/NoteCard.vue - Card export integration
resources/js/components/notes/NoteViewDialog.vue - Dialog export integration
```

### Key Functions
- `exportNote(note, format)` - Export a single note
- `exportNotes(notes, format)` - Export multiple notes
- `generateMarkdown(note)` - Generate markdown content
- `generateCSV(notes)` - Generate CSV content
- `generateTextFile(note)` - Generate text content
- `downloadFile(content, filename, mimeType)` - Trigger browser download

### Dependencies
- Uses browser Blob API for file creation
- Uses URL.createObjectURL for download triggers
- No external libraries required

## Security Considerations
- Export happens entirely on the client side
- No server requests are made during export
- Files contain only data that the user already has access to
- Filenames are sanitized to prevent directory traversal

## Browser Compatibility
- Works in all modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript to be enabled
- Uses standard browser download mechanism
