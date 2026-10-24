// Gerak landing page (L10). Dimuat hanya di landing, dan tidak dijalankan untuk pengguna "kurangi gerakan".
// Semua isi tetap terlihat kalau skrip ini gagal dimuat: GSAP hanya menganimasikan dari keadaan awal ke tampilan normal.
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import 'lenis/dist/lenis.css';

export default function mulaiGerakLanding() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    gsap.registerPlugin(ScrollTrigger);

    // Gulir halus; tautan #program, #galeri, dan lainnya ikut digulir Lenis
    const lenis = new Lenis({ anchors: true, autoRaf: false });
    window.lenis = lenis;
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((waktu) => lenis.raf(waktu * 1000));
    gsap.ticker.lagSmoothing(0);

    // Momen utama: judul naik dari balik garis, foto terbuka dari bawah, lalu teks dan tombol
    gsap.timeline({ defaults: { ease: 'expo.out' } })
        .from('[data-hero-baris]', { yPercent: 110, duration: 1, stagger: 0.1 })
        .from('[data-hero-foto]', { clipPath: 'inset(100% 0% 0% 0% round 28px)', duration: 1.1, ease: 'power3.inOut' }, 0.05)
        .from('[data-hero-isi] > *', { y: 18, opacity: 0, duration: 0.6, stagger: 0.08 }, 0.4);

    // Linimasa sejarah: garis oranye mengikuti gulir, titik tahun terisi saat garis melewatinya
    const linimasa = document.querySelector('[data-linimasa]');
    if (linimasa) {
        gsap.fromTo('[data-linimasa-isi]', { scaleY: 0 }, {
            scaleY: 1, ease: 'none',
            scrollTrigger: { trigger: linimasa, start: 'top 70%', end: 'bottom 60%', scrub: 0.4 },
        });
        linimasa.querySelectorAll('[data-linimasa-titik]').forEach((titik) => {
            const bulat = titik.firstElementChild;
            bulat.classList.remove('lewat');
            ScrollTrigger.create({ trigger: titik, start: 'top 62%', onEnter: () => bulat.classList.add('lewat'), onLeaveBack: () => bulat.classList.remove('lewat') });
            gsap.from(titik.lastElementChild, { x: -12, opacity: 0, duration: 0.5, ease: 'power2.out', scrollTrigger: { trigger: titik, start: 'top 80%', once: true } });
        });
    }

    // Kartu turnamen muncul berurutan sekali, sebagai satu daftar (bagian ini hanya ada kalau sudah ada turnamen)
    if (document.querySelector('[data-prestasi]')) {
        gsap.from('[data-prestasi] > li', {
            y: 28, opacity: 0, duration: 0.6, stagger: 0.08, ease: 'power3.out',
            scrollTrigger: { trigger: '[data-prestasi]', start: 'top 80%', once: true },
        });
    }
}
