<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get template by slug.
     */
    public static function getBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Render template with variables.
     */
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Replace variables in subject and body
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        // Also replace app settings
        $subject = str_replace('{{app_name}}', Setting::get('app_name', config('app.name')), $subject);
        $body = str_replace('{{app_name}}', Setting::get('app_name', config('app.name')), $body);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Get available variables as formatted string.
     */
    public function getVariablesListAttribute(): string
    {
        if (empty($this->variables)) {
            return '-';
        }

        return collect($this->variables)->map(fn($v) => '{{' . $v . '}}')->implode(', ');
    }
}
