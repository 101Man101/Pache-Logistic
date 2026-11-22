import { onMounted, onUnmounted } from 'vue';

/**
 * Hands-free USB/Bluetooth HID Barcode Scanner listener with Web Audio beep feedback.
 */
export function useBarcodeScanner(
    onScan: (code: string) => void,
    minLength = 6,
): { playBeep: (success: boolean) => void } {
    let buffer = '';
    let lastKeystrokeAt = 0;

    function playBeep(success: boolean): void {
        if (typeof window === 'undefined') {
            return;
        }
        try {
            const AudioCtx = window.AudioContext;
            if (!AudioCtx) {
                return;
            }
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = success ? 'sine' : 'sawtooth';
            osc.frequency.setValueAtTime(success ? 880 : 220, ctx.currentTime);
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + (success ? 0.1 : 0.28));
        } catch {
            // Audio context unavailable or blocked by autoplay policy
        }
    }

    function handleKeyDown(e: KeyboardEvent): void {
        const target = e.target as HTMLElement | null;
        if (
            target &&
            ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)
        ) {
            return;
        }

        const now = Date.now();
        if (now - lastKeystrokeAt > 120) {
            buffer = '';
        }
        lastKeystrokeAt = now;

        if (e.key === 'Enter') {
            if (buffer.length >= minLength) {
                e.preventDefault();
                onScan(buffer.trim());
            }
            buffer = '';
            return;
        }

        if (e.key.length === 1) {
            buffer += e.key;
        }
    }

    onMounted(() => window.addEventListener('keydown', handleKeyDown));
    onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));

    return { playBeep };
}
