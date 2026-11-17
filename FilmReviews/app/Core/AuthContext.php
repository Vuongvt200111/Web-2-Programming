<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\MediaService;

/**
 * Immutable representation of the current tab-isolated authentication state.
 */
final class AuthContext
{
    private ?int $userId;
    private int $role;
    private bool $isBoss;
    private string $name;
    private string $email;
    private ?string $avatar;

    public function __construct(
        ?int $userId,
        int $role,
        bool $isBoss,
        string $name,
        string $email,
        ?string $avatar
    ) {
        $this->userId = $userId;
        $this->role = $role;
        $this->isBoss = $isBoss;
        $this->name = $name;
        $this->email = $email;
        $this->avatar = $avatar;
    }

    public static function fromSession(): self
    {
        $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        if ($uid === null || $uid <= 0) {
            return new self(null, 0, false, 'Guest', '', null);
        }

        $role = (int) ($_SESSION['role'] ?? 0);
        $isBoss = !empty($_SESSION['is_boss']) || $role === 1 || $uid === 1;

        return new self(
            $uid,
            $role,
            $isBoss,
            (string) ($_SESSION['name'] ?? 'User'),
            (string) ($_SESSION['email'] ?? ''),
            !empty($_SESSION['avatar']) ? (string) $_SESSION['avatar'] : null
        );
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null && $this->userId > 0;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function role(): int
    {
        return $this->role;
    }

    public function isBoss(): bool
    {
        return $this->isBoss;
    }

    public function isAdmin(): bool
    {
        return $this->isAuthenticated() && ($this->isBoss || $this->role >= 1);
    }

    public function isReviewer(): bool
    {
        return $this->isAuthenticated() && !$this->isBoss && $this->role === 0;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function avatar(): ?string
    {
        return $this->avatar;
    }

    public static function isBossRecord(array $user): bool
    {
        $id = (int) ($user['id'] ?? 0);
        $role = (int) ($user['role'] ?? 0);
        return $id === 1 || $role === 1;
    }

    public static function isAdminRecord(array $user): bool
    {
        return self::isBossRecord($user) || ((int) ($user['role'] ?? 0) >= 1) || (($user['role'] ?? '') === 'admin');
    }

    /**
     * Normalized array consumed by layouts and templates with pre-resolved avatar metadata.
     *
     * @return array<string, mixed>
     */
    public function toViewArray(MediaService $media, string $baseUrl): array
    {
        $avatarMeta = $media->resolveAvatar($this->avatar, $this->name, $baseUrl);

        return [
            'is_authenticated' => $this->isAuthenticated(),
            'user_id'          => $this->userId ?? 0,
            'role'             => $this->role,
            'is_boss'          => $this->isBoss,
            'is_admin'         => $this->isAdmin(),
            'is_reviewer'      => $this->isReviewer(),
            'name'             => $this->name,
            'email'            => $this->email,
            'avatar'           => $this->avatar,
            'avatar_meta'      => $avatarMeta,
        ];
    }
}
