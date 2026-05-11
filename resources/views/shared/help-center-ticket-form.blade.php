@php
    $ticketCategories = ['Inquiry', 'Request', 'Feedback', 'Complaint', 'Concern', 'Technical'];
    $ticketPriorities = ['Low', 'Normal', 'High', 'Urgent'];
@endphp

<form method="POST" action="{{ $helpTicketStoreRoute }}" class="reg-help-ticket-form">
    @csrf
    <div class="reg-help-ticket-grid">
        <label>
            <span>Name</span>
            <input type="text" name="requester_name" value="{{ old('requester_name', $helpTicketUserName ?? '') }}" required>
        </label>
        <label>
            <span>Email</span>
            <input type="email" name="requester_email" value="{{ old('requester_email', $helpTicketUserEmail ?? '') }}">
        </label>
        <label>
            <span>Category</span>
            <select name="category" required>
                @foreach($ticketCategories as $category)
                    <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Priority</span>
            <select name="priority" required>
                @foreach($ticketPriorities as $priority)
                    <option value="{{ $priority }}" {{ old('priority', 'Normal') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <label class="reg-help-ticket-full">
        <span>Subject</span>
        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Brief summary of your concern" required>
    </label>

    <label class="reg-help-ticket-full">
        <span>Message</span>
        <textarea name="message" rows="5" placeholder="Describe the issue, request, or feedback." required>{{ old('message') }}</textarea>
    </label>

    <div class="reg-help-ticket-actions">
        <button type="submit" class="reg-help-ticket-submit">Submit Ticket</button>
    </div>
</form>
