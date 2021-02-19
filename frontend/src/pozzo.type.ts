
export type SiteConfig = {
    apiUri: string,
    siteTitle: string|boolean,
    formats: string[],
    sizes: any[],
    promo: boolean,
    maxUploadBytes: number,
}

export type FrontendState = {
    fullScreen: boolean,
    photoToolsVisible: boolean,
    nextPhotoLink: string,
    prevPhotoLink: string,
    isMetadataOn: boolean,
    userStoppedUploadScroll: boolean,
}

export type ApiResult = {
    success: boolean,
    code: number,
    data: any,
}

export type ApiOptions = {
    authorize?: boolean,
    params?: object,
    method?: string,
}

export type FileUploadStatus = {
    file: File,
    status: number,
    index: number,
    targetAlbum?: number,
    startUploadCallback?: Function,
}

// only the data needed to render an image
//   (and even the title just gets used as an alt
//   tag so could get swapped out to something else)
export type PhotoStub = {
    id: number,
    title?: string,
    hash: string,
    uniq: string,
    blurHash: string,
    aspect: number
}

export type Photo = {
    // recapitulates stub, but now title is required
    id: number,
    title: string,
    hash: string,
    uniq: string,
    blurHash: string,
    aspect: number

    // extended import data
    uploadTimeStamp: number,
    uploadedBy: number,
    originalFilename: string,
    size: number,
    width: number,
    height: number,

    // pulled from exif, so if the uploaded photo
    //   didn't have it, it ain't here
    make: string|null,
    model: string|null,
    lens: string|null,
    mime: string|null,
    creationDate: number|null,
    keywords: string|null,
    subjectArea: string|null,
    aperture: string|null,
    iso: string|null,
    shutterSpeed: string|null,
    gpsLat: number|null,
    gpsLon: number|null,
    gpsAlt: number|null,
}

export type Album = {
    id: number,
    title: string,
    description: string,
    slug: string,
    isPrivate: boolean,
    showMap: boolean,
    coverPhoto: number,
    coverHash?: string,
    coverUniq?: string,
    coverAspect?: number,
    coverBlurHash?: string,
    highestIndex: number,
    photos: PhotoStub[]
}
