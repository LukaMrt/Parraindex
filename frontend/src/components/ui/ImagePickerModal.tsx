import { useCallback, useEffect, useRef, useState } from 'react';
import type { DragEvent, SyntheticEvent } from 'react';
import ReactCrop, { centerCrop, makeAspectCrop } from 'react-image-crop';
import type { Crop, PixelCrop } from 'react-image-crop';
import 'react-image-crop/dist/ReactCrop.css';
import { Button } from './Button';
import { Modal } from './Modal';

const OUTPUT_SIZE = 512;

function centerAspectCrop(w: number, h: number): Crop {
  return centerCrop(makeAspectCrop({ unit: '%', width: 90 }, 1, w, h), w, h);
}

async function cropToFile(
  img: HTMLImageElement,
  crop: PixelCrop,
  originalName: string,
): Promise<File> {
  const canvas = document.createElement('canvas');
  canvas.width = OUTPUT_SIZE;
  canvas.height = OUTPUT_SIZE;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('canvas 2d context unavailable');

  const scaleX = img.naturalWidth / img.width;
  const scaleY = img.naturalHeight / img.height;

  ctx.drawImage(
    img,
    crop.x * scaleX,
    crop.y * scaleY,
    crop.width * scaleX,
    crop.height * scaleY,
    0,
    0,
    OUTPUT_SIZE,
    OUTPUT_SIZE,
  );

  return new Promise((resolve, reject) => {
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          reject(new Error('canvas toBlob failed'));
          return;
        }
        resolve(new File([blob], originalName.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' }));
      },
      'image/jpeg',
      0.92,
    );
  });
}

interface Props {
  open: boolean;
  onClose: () => void;
  onConfirm: (file: File, preview: string) => void;
}

export function ImagePickerModal({ open, onClose, onConfirm }: Props) {
  const [src, setSrc] = useState<string | null>(null);
  const [isGif, setIsGif] = useState(false);
  const [originalFile, setOriginalFile] = useState<File | null>(null);
  const [crop, setCrop] = useState<Crop>();
  const [completedCrop, setCompletedCrop] = useState<PixelCrop>();
  const [dragOver, setDragOver] = useState(false);
  const [confirming, setConfirming] = useState(false);
  const imgRef = useRef<HTMLImageElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    return () => {
      if (src) URL.revokeObjectURL(src);
    };
  }, [src]);

  function reset() {
    setSrc(null);
    setIsGif(false);
    setOriginalFile(null);
    setCrop(undefined);
    setCompletedCrop(undefined);
  }

  function handleClose() {
    reset();
    onClose();
  }

  const loadFile = useCallback((file: File) => {
    setSrc((prev) => {
      if (prev) URL.revokeObjectURL(prev);
      return URL.createObjectURL(file);
    });
    setIsGif(file.type === 'image/gif');
    setOriginalFile(file);
    setCrop(undefined);
    setCompletedCrop(undefined);
  }, []);

  const handleDrop = useCallback(
    (e: DragEvent) => {
      e.preventDefault();
      setDragOver(false);
      const file = e.dataTransfer.files[0];
      if (file?.type.startsWith('image/')) loadFile(file);
    },
    [loadFile],
  );

  function onImageLoad(e: SyntheticEvent<HTMLImageElement>) {
    const { naturalWidth: w, naturalHeight: h } = e.currentTarget;
    setCrop(centerAspectCrop(w, h));
  }

  async function handleConfirm() {
    if (!originalFile || !src) return;

    if (isGif) {
      onConfirm(originalFile, src);
      handleClose();
      return;
    }

    if (!imgRef.current || !completedCrop) return;
    setConfirming(true);
    try {
      const file = await cropToFile(imgRef.current, completedCrop, originalFile.name);
      const preview = URL.createObjectURL(file);
      onConfirm(file, preview);
      handleClose();
    } finally {
      setConfirming(false);
    }
  }

  return (
    <Modal open={open} onClose={handleClose} maxWidth="600px" className="p-0 overflow-hidden">
      <div className="flex items-center justify-between border-b border-line px-6 py-4">
        <h2 className="text-[15px] font-semibold text-ink">Choisir une photo</h2>
        <button
          type="button"
          onClick={handleClose}
          className="rounded-lg p-1 text-ink-3 transition-colors hover:bg-bg hover:text-ink cursor-pointer"
          aria-label="Fermer"
        >
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <div className="p-6">
        {!src ? (
          <div
            onDragOver={(e) => {
              e.preventDefault();
              setDragOver(true);
            }}
            onDragEnter={() => {
              setDragOver(true);
            }}
            onDragLeave={() => {
              setDragOver(false);
            }}
            onDrop={handleDrop}
            onClick={() => inputRef.current?.click()}
            className={`flex cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed py-14 transition-colors ${
              dragOver ? 'border-ink-2 bg-bg' : 'border-line bg-bg hover:border-ink-3'
            }`}
          >
            <svg
              width="36"
              height="36"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="1.5"
              strokeLinecap="round"
              strokeLinejoin="round"
              className="text-ink-3"
            >
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
            </svg>
            <div className="text-center">
              <p className="text-[13.5px] font-medium text-ink">Glissez une image ici</p>
              <p className="mt-0.5 text-[12px] text-ink-4">
                ou cliquez pour parcourir · JPG, PNG, GIF, WebP…
              </p>
            </div>
          </div>
        ) : (
          <div className="flex flex-col items-center gap-4">
            {isGif ? (
              <div className="overflow-hidden rounded-xl border border-line">
                <img src={src} alt="Aperçu GIF" className="max-h-72 object-contain" />
              </div>
            ) : (
              <div className="w-full overflow-auto rounded-xl border border-line bg-bg flex items-center justify-center p-2">
                <ReactCrop
                  crop={crop}
                  onChange={(c) => {
                    setCrop(c);
                  }}
                  onComplete={(c) => {
                    setCompletedCrop(c);
                  }}
                  aspect={1}
                  circularCrop={false}
                  className="max-h-[360px]"
                >
                  <img
                    ref={imgRef}
                    src={src}
                    alt="Crop"
                    onLoad={onImageLoad}
                    className="max-h-[360px] w-auto"
                  />
                </ReactCrop>
              </div>
            )}

            {isGif && (
              <p className="text-[11.5px] text-ink-4">
                Les GIF animés sont uploadés sans recadrage.
              </p>
            )}

            <Button
              type="button"
              variant="secondary"
              size="sm"
              onClick={() => inputRef.current?.click()}
            >
              Choisir une autre image
            </Button>
          </div>
        )}

        <input
          ref={inputRef}
          type="file"
          accept="image/*"
          className="hidden"
          onChange={(e) => {
            const file = e.target.files?.[0];
            if (file) loadFile(file);
            e.target.value = '';
          }}
        />
      </div>

      <div className="flex justify-end gap-2 border-t border-line px-6 py-4">
        <Button type="button" variant="ghost" onClick={handleClose}>
          Annuler
        </Button>
        <Button
          type="button"
          disabled={!src || confirming || (!isGif && !completedCrop)}
          onClick={() => void handleConfirm()}
        >
          {confirming ? 'Traitement…' : 'Utiliser cette photo'}
        </Button>
      </div>
    </Modal>
  );
}
